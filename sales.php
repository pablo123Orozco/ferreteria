<?php
require_once __DIR__ . '/auth.php';

$res = $mysqli->query("
  SELECT s.id, s.sale_date, s.total, s.note, u.username
  FROM sales s
  JOIN users u ON u.id = s.user_id
  ORDER BY s.id DESC
  LIMIT 200
");
$rows = $res->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/layouts/header.php';
?>
<h3>Ventas</h3>
<table border="1" cellpadding="6">
  <tr><th>#</th><th>Fecha</th><th>Usuario</th><th>Total</th><th>Nota</th><th></th></tr>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= (int)$r['id'] ?></td>
      <td><?= h($r['sale_date']) ?></td>
      <td><?= h($r['username']) ?></td>
      <td>Q <?= number_format((float)$r['total'],2) ?></td>
      <td><?= h((string)$r['note']) ?></td>
      <td><a href="sale_view.php?id=<?=(int)$r['id']?>">Ver</a></td>
    </tr>
  <?php endforeach; ?>
</table>
<?php include __DIR__ . '/layouts/footer.php'; ?>