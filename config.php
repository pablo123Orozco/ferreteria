<?php
declare(strict_types=1);
session_start();

/**
 * Recomendado: no hardcodear credenciales.
 * En Azure App Service -> Variables de entorno:
 * DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT
 */

$DB_HOST = getenv('DB_HOST') ?: 'ferreteria-mysql-server.mysql.database.azure.com';
$DB_NAME = getenv('DB_NAME') ?: 'ferreteria';

// OJO Azure: a veces necesita usuario@servidor.
// Si te falla, usa: Pablo123@ferreteria-mysql-server
$DB_USER = getenv('DB_USER') ?: 'Pablo123';
$DB_PASS = getenv('DB_PASS') ?: 'ProyectoSeguridad2026';
$DB_PORT = (int)(getenv('DB_PORT') ?: 3306);

// Opcional: fuerza SSL
$FORCE_SSL = (getenv('DB_FORCE_SSL') ?: '1') === '1';
$ssl_ca = __DIR__ . '/certs/DigiCertGlobalRootCA.crt.pem';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  $mysqli = mysqli_init();

  // Timeouts prudentes
  $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

  if ($FORCE_SSL) {
    if (!file_exists($ssl_ca)) {
      throw new RuntimeException("Falta CA SSL en $ssl_ca. Sube el certificado o desactiva DB_FORCE_SSL.");
    }
    mysqli_ssl_set($mysqli, null, null, $ssl_ca, null, null);
    $mysqli->real_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT, null, MYSQLI_CLIENT_SSL);
  } else {
    // Solo si tu servidor permite no-SSL
    $mysqli->real_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
  }

  $mysqli->set_charset('utf8mb4');
} catch (Throwable $e) {
  http_response_code(500);
  exit("Error de conexión a la BD: " . htmlspecialchars($e->getMessage()));
}

// helpers
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function require_login(): void {
  if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
  }
}

function require_role(string $role): void {
  require_login();
  if (($_SESSION['user']['role'] ?? '') !== $role) {
    http_response_code(403);
    exit('No autorizado.');
  }
}