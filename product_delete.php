<?php
require_once __DIR__ . '/auth.php';
require_role('admin'); // solo admin

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
  $stmt = $mysqli->prepare("UPDATE products SET active=0 WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
}
header("Location: products.php");
exit;