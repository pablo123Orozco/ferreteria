<?php
require_once __DIR__ . '/auth.php';

$id = (int)($_POST['id'] ?? 0);
$sku = trim($_POST['sku'] ?? '');
$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$min_stock = (int)($_POST['min_stock'] ?? 0);
$active = (int)($_POST['active'] ?? 1);

if ($id > 0) {
  $stmt = $mysqli->prepare("UPDATE products SET sku=?, name=?, category=?, price=?, stock=?, min_stock=?, active=? WHERE id=?");
  $stmt->bind_param("sssdiiii", $sku, $name, $category, $price, $stock, $min_stock, $active, $id);
  $stmt->execute();
} else {
  $stmt = $mysqli->prepare("INSERT INTO products (sku,name,category,price,stock,min_stock,active) VALUES (?,?,?,?,?,?,?)");
  $stmt->bind_param("sssdiii", $sku, $name, $category, $price, $stock, $min_stock, $active);
  $stmt->execute();
}

header("Location: products.php");
exit;