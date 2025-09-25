<?php
// script para importar un archivo SQL simple desde PHP usando PDO
// Uso: php scripts/import_backup.php [ruta/al/archivo.sql]

$path = $argv[1] ?? __DIR__ . '/../backup_database.sql';
if (!file_exists($path)) {
    echo "Archivo no encontrado: $path\n";
    exit(1);
}

$sql = file_get_contents($path);
if ($sql === false) {
    echo "No se pudo leer el archivo $path\n";
    exit(1);
}

// Conexión al servidor MySQL (ajusta si usas credenciales distintas)
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';

try {
    // Conectar sin DB para poder ejecutar CREATE DATABASE y USE
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}

// Normalizar saltos de línea
$sql = str_replace(["\r\n", "\r"], "\n", $sql);
$lines = explode("\n", $sql);
$statements = [];
$buffer = '';
foreach ($lines as $line) {
    $trim = ltrim($line);
    // ignorar comentarios que empiecen por -- o por #
    if (strpos($trim, '--') === 0 || strpos($trim, '#') === 0) {
        continue;
    }
    $buffer .= $line . "\n";
    // dividir por punto y coma al final de línea
    if (preg_match('/;\s*$/', $line)) {
        $statements[] = $buffer;
        $buffer = '';
    }
}
// si quedó algo
if (trim($buffer) !== '') {
    $statements[] = $buffer;
}

echo "Encontradas " . count($statements) . " sentencias.\n";

foreach ($statements as $i => $stmt) {
    $stmtTrim = trim($stmt);
    if ($stmtTrim === '') continue;
    try {
        $pdo->exec($stmtTrim);
        echo "OK: sentencia " . ($i+1) . " ejecutada.\n";
    } catch (PDOException $e) {
        echo "ERROR en sentencia " . ($i+1) . ": " . $e->getMessage() . "\n";
        // continuar con siguientes
    }
}

echo "Importación finalizada.\n";
