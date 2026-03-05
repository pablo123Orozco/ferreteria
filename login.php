<?php
require_once __DIR__ . "/config.php";

if (!empty($_SESSION['user'])) {
  header("Location: index.php");
  exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = $mysqli->prepare("SELECT id, username, password_hash, role FROM users WHERE username=? LIMIT 1");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();

  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user'] = ['id'=>(int)$user['id'], 'username'=>$user['username'], 'role'=>$user['role']];
    header("Location: index.php");
    exit;
  }
  $error = "Usuario o contraseña incorrectos.";
}

include __DIR__ . "/layouts/header.php";
?>
<h3>Login</h3>
<?php if($error): ?><div style="color:red;"><?=h($error)?></div><?php endif; ?>
<form method="post">
  <input name="username" placeholder="Usuario" required>
  <input name="password" type="password" placeholder="Contraseña" required>
  <button>Entrar</button>
</form>
<?php include __DIR__ . "/layouts/footer.php"; ?>