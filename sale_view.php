<?php
require_once __DIR__ . '/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $mysqli->prepare("
  SELECT s.id, s.sale_date, s.total, s.note, u.username
  FROM sales s
  JOIN users u ON u.id=s.user_id
  WHERE s.id=? LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();
if (!$sale) { http_response_code(404); exit("Venta no encontrada"); }

$stmt2 = $mysqli->prepare("
  SELECT si.qty, si.unit_price, si.line_total, p.sku, p.name
  FROM sale_items si
  JOIN products p ON p.id=si.product_id
  WHERE si.sale_id=?
");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/layouts/header.php';
?>
<h3>Venta #<?= (int)$sale['id'] ?></h3>
<div>Fecha: <?= h($sale['sale_date']) ?></div>
<div>Usuario: <?= h($sale['username']) ?></div>
<div>Nota: <?= h((string)$sale['note']) ?></div>
<div><b>Total: Q <?= number_format((float)$sale['total'],2) ?></b></div>

<h4>Items</h4>
<table border="1" cellpadding="6">
  <tr><th>SKU</th><th>Producto</th><th>Cant.</th><th>P.Unit</th><th>Total</th></tr>
  <?php foreach($items as $it): ?>
    <tr>
      <td><?= h($it['sku']) ?></td>
      <td><?= h($it['name']) ?></td>
      <td><?= (int)$it['qty'] ?></td>
      <td><?= number_format((float)$it['unit_price'],2) ?></td>
      <td><?= number_format((float)$it['line_total'],2) ?></td>
    </tr>
  <?php endforeach; ?>
</table>

<a href="sales.php">Volver</a>
<?php include __DIR__ . '/layouts/footer.php'; ?>