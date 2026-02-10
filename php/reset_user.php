<?php
include 'conexion.php';

// Limpiamos la tabla para no tener duplicados raros
mysqli_query($conexion, "DELETE FROM usuarios WHERE usuario = 'admin'");

$nombre = "Administrador";
$user = "admin";
$pass = "admin123";
// El propio PHP genera el hash aquí mismo
$hash = password_hash($pass, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?, ?, ?, 'admin')";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sss", $nombre, $user, $hash);

if ($stmt->execute()) {
    echo "✅ Usuario 'admin' re-creado con éxito.<br>";
    echo "Contraseña: <b>admin123</b><br>";
    echo "Hash generado: " . $hash;
} else {
    echo "❌ Error: " . $conexion->error;
}
?>