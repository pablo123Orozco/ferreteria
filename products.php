<?php
require_once __DIR__ . '/auth.php';

$q = trim($_GET['q'] ?? '');
$activeOnly = ($_GET['active'] ?? '1') === '1';

$sql = "SELECT id, sku, name, category, price, stock, min_stock, active
        FROM products
        WHERE 1=1 ";
$params = [];
$types = "";

if ($activeOnly) { $sql .= " AND active=1 "; }
if ($q !== '') {
  $sql .= " AND (sku LIKE CONCAT('%',?,'%') OR name LIKE CONCAT('%',?,'%') OR category LIKE CONCAT('%',?,'%')) ";
  $params = [$q, $q, $q];
  $types = "sss";
}
$sql .= " ORDER BY id DESC LIMIT 500";

$stmt = $mysqli->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/layouts/header.php';
?>
<h3>Productos</h3>
<form>
  <input name="q" value="<?=h($q)?>" placeholder="Buscar SKU / nombre / categoría">
  <label><input type="checkbox" name="active" value="1" <?= $activeOnly?'checked':''; ?>> Solo activos</label>
  <button>Buscar</button>
  <a href="product_form.php">+ Nuevo</a>
</form>

<table border="1" cellpadding="6">
  <tr>
    <th>ID</th><th>SKU</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Mínimo</th><th>Activo</th><th>Acciones</th>
  </tr>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= (int)$r['id'] ?></td>
      <td><?= h($r['sku']) ?></td>
      <td><?= h($r['name']) ?></td>
      <td><?= h((string)$r['category']) ?></td>
      <td><?= number_format((float)$r['price'],2) ?></td>
      <td><?= (int)$r['stock'] ?></td>
      <td><?= (int)$r['min_stock'] ?></td>
      <td><?= (int)$r['active'] ?></td>
      <td>
        <a href="product_form.php?id=<?=(int)$r['id']?>">Editar</a>
        <a href="product_delete.php?id=<?=(int)$r['id']?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<?php include __DIR__ . '/layouts/footer.php'; ?>