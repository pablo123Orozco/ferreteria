<?php
require_once __DIR__ . '/auth.php';

$id = (int)($_GET['id'] ?? 0);
$row = ['sku'=>'','name'=>'','category'=>'','price'=>'0.00','stock'=>0,'min_stock'=>0,'active'=>1];

if ($id > 0) {
  $stmt = $mysqli->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $found = $stmt->get_result()->fetch_assoc();
  if ($found) $row = $found;
}

include __DIR__ . '/layouts/header.php';
?>
<h3><?= $id? "Editar":"Nuevo" ?> Producto</h3>
<form method="post" action="product_save.php">
  <input type="hidden" name="id" value="<?= (int)$id ?>">
  <div>SKU: <input name="sku" value="<?=h($row['sku'])?>" required></div>
  <div>Nombre: <input name="name" value="<?=h($row['name'])?>" required></div>
  <div>Categoría: <input name="category" value="<?=h((string)$row['category'])?>"></div>
  <div>Precio: <input name="price" type="number" step="0.01" value="<?=h((string)$row['price'])?>" required></div>
  <div>Stock: <input name="stock" type="number" value="<?= (int)$row['stock'] ?>" required></div>
  <div>Mínimo: <input name="min_stock" type="number" value="<?= (int)$row['min_stock'] ?>" required></div>
  <div>Activo:
    <select name="active">
      <option value="1" <?= ((int)$row['active']===1)?'selected':''; ?>>Sí</option>
      <option value="0" <?= ((int)$row['active']===0)?'selected':''; ?>>No</option>
    </select>
  </div>
  <button>Guardar</button>
  <a href="products.php">Volver</a>
</form>
<?php include __DIR__ . '/layouts/footer.php'; ?>