<?php
require_once __DIR__ . '/auth.php';

$res = $mysqli->query("SELECT id, sku, name, price, stock FROM products WHERE active=1 ORDER BY name ASC");
$products = $res->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/layouts/header.php';
?>
<h3>POS (Venta)</h3>

<form method="post" action="pos_checkout.php">
  <table border="1" cellpadding="6">
    <tr><th>Producto</th><th>Precio</th><th>Stock</th><th>Cantidad</th></tr>
    <?php foreach($products as $p): ?>
      <tr>
        <td><?=h($p['name'])?> (SKU <?=h($p['sku'])?>)</td>
        <td><?=number_format((float)$p['price'],2)?></td>
        <td><?= (int)$p['stock'] ?></td>
        <td>
          <input type="number" min="0" name="qty[<?= (int)$p['id']?>]" value="0">
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <div>
    Nota: <input name="note" placeholder="Venta mostrador">
  </div>
  <button>Finalizar venta</button>
</form>

<?php include __DIR__ . '/layouts/footer.php'; ?>