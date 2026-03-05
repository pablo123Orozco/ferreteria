<?php
require_once __DIR__ . "/auth.php";

// KPIs rápidos

// Total productos activos
$res = $mysqli->query("SELECT COUNT(*) AS c FROM products WHERE active=1");
$totalProducts = (int)($res->fetch_assoc()['c'] ?? 0);

// Productos con bajo stock
$res = $mysqli->query("SELECT COUNT(*) AS c FROM products WHERE active=1 AND stock <= min_stock");
$lowStock = (int)($res->fetch_assoc()['c'] ?? 0);

// Ventas de hoy
$res = $mysqli->query("SELECT COALESCE(SUM(total),0) AS t FROM sales WHERE DATE(sale_date)=CURDATE()");
$salesToday = (float)($res->fetch_assoc()['t'] ?? 0.0);

// Últimas ventas
$stmt = $mysqli->prepare("
SELECT s.id, s.sale_date, s.total, u.username
FROM sales s
JOIN users u ON u.id = s.user_id
ORDER BY s.id DESC
LIMIT 5
");
$stmt->execute();
$lastSales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . "/layouts/header.php";
?>

<h2>Panel Principal</h2>

<div style="display:flex;gap:20px;margin-bottom:30px">

<div style="padding:20px;border:1px solid #ccc">
<h3>Productos activos</h3>
<h1><?= $totalProducts ?></h1>
</div>

<div style="padding:20px;border:1px solid #ccc">
<h3>Bajo stock</h3>
<h1><?= $lowStock ?></h1>
</div>

<div style="padding:20px;border:1px solid #ccc">
<h3>Ventas hoy</h3>
<h1>Q <?= number_format($salesToday,2) ?></h1>
</div>

</div>

<h3>Últimas ventas</h3>

<table border="1" cellpadding="8">
<tr>
<th>ID</th>
<th>Fecha</th>
<th>Usuario</th>
<th>Total</th>
</tr>

<?php if(!$lastSales): ?>
<tr>
<td colspan="4">No hay ventas registradas.</td>
</tr>
<?php endif; ?>

<?php foreach($lastSales as $s): ?>

<tr>
<td><?= (int)$s['id'] ?></td>
<td><?= htmlspecialchars($s['sale_date']) ?></td>
<td><?= htmlspecialchars($s['username']) ?></td>
<td>Q <?= number_format((float)$s['total'],2) ?></td>
</tr>

<?php endforeach; ?>

</table>

<br>

<h3>Accesos rápidos</h3>

<ul>
<li><a href="products.php">📦 Productos</a></li>
<li><a href="pos.php">🛒 Nueva venta</a></li>
<li><a href="sales.php">📊 Reporte de ventas</a></li>
<li><a href="logout.php">🚪 Cerrar sesión</a></li>
</ul>

<?php include __DIR__ . "/layouts/footer.php"; ?>