<?php
require_once __DIR__ . '/auth.php';

$userId = (int)($_SESSION['user']['id'] ?? 0);
$note = trim($_POST['note'] ?? '');
$qtyMap = $_POST['qty'] ?? [];

$items = [];
foreach ($qtyMap as $productId => $qty) {
  $pid = (int)$productId;
  $q = (int)$qty;
  if ($pid > 0 && $q > 0) $items[] = ['product_id'=>$pid, 'qty'=>$q];
}

if (!$items) {
  header("Location: pos.php");
  exit;
}

try {
  $mysqli->begin_transaction();

  // 1) Leer productos (precio/stock) y validar stock
  $total = 0.0;

  $stmtP = $mysqli->prepare("SELECT id, price, stock FROM products WHERE id=? AND active=1 FOR UPDATE");
  $stmtUpdateStock = $mysqli->prepare("UPDATE products SET stock = stock - ? WHERE id=?");
  $stmtSaleItem = $mysqli->prepare("INSERT INTO sale_items (sale_id, product_id, qty, unit_price, line_total) VALUES (?,?,?,?,?)");
  $stmtMove = $mysqli->prepare("INSERT INTO stock_movements (product_id, movement_type, qty, reference_table, reference_id, note)
                                VALUES (?, 'OUT', ?, 'sales', ?, ?)");

  // 2) Crear sale
  $stmtSale = $mysqli->prepare("INSERT INTO sales (user_id, total, note) VALUES (?, 0.00, ?)");
  $stmtSale->bind_param("is", $userId, $note);
  $stmtSale->execute();
  $saleId = (int)$mysqli->insert_id;

  foreach ($items as $it) {
    $pid = (int)$it['product_id'];
    $qty = (int)$it['qty'];

    $stmtP->bind_param("i", $pid);
    $stmtP->execute();
    $p = $stmtP->get_result()->fetch_assoc();
    if (!$p) throw new RuntimeException("Producto inválido ID=$pid");

    $price = (float)$p['price'];
    $stock = (int)$p['stock'];
    if ($qty > $stock) throw new RuntimeException("Stock insuficiente en producto ID=$pid (stock=$stock, requerido=$qty)");

    $lineTotal = $price * $qty;
    $total += $lineTotal;

    // rebajar stock
    $stmtUpdateStock->bind_param("ii", $qty, $pid);
    $stmtUpdateStock->execute();

    // insertar item
    $stmtSaleItem->bind_param("iiidd", $saleId, $pid, $qty, $price, $lineTotal);
    $stmtSaleItem->execute();

    // movimiento OUT
    $moveNote = $note !== '' ? $note : 'Salida por venta';
    $stmtMove->bind_param("iiis", $pid, $qty, $saleId, $moveNote);
    $stmtMove->execute();
  }

  // 3) Actualizar total en sales
  $stmtUpd = $mysqli->prepare("UPDATE sales SET total=? WHERE id=?");
  $stmtUpd->bind_param("di", $total, $saleId);
  $stmtUpd->execute();

  $mysqli->commit();
  header("Location: sale_view.php?id=$saleId");
  exit;

} catch (Throwable $e) {
  $mysqli->rollback();
  http_response_code(400);
  exit("Error al procesar venta: " . h($e->getMessage()));
}