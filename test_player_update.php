<?php
// Script de prueba para verificar el logging de actualización de jugadores

$url = 'http://localhost/players/1'; // Ajusta la URL según tu configuración
$data = [
    'nombre' => 'Lionel',
    'apellidos' => 'Messi',
    'dorsal' => 10,
    'salario' => 1000000,
    'id_club' => 'BAR' // Ajusta según tus datos
];

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'PUT',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Respuesta del servidor:\n";
echo $result . "\n\n";

echo "Contenido del log:\n";
if (file_exists('var/log/player_debug.log')) {
    echo file_get_contents('var/log/player_debug.log');
} else {
    echo "El archivo de log no existe.\n";
}
?>
