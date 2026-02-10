<?php
include 'conexion.php';

$usuario = 'admin';
$password_plana = 'admin123';
$nuevo_hash = password_hash($password_plana, PASSWORD_DEFAULT);

$sql = "UPDATE usuarios SET password = ? WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $nuevo_hash, $usuario);

if ($stmt->execute()) {
    echo "✅ El hash ha sido actualizado correctamente.<br>";
    echo "Hash guardado: " . $nuevo_hash;
} else {
    echo "❌ Error al actualizar: " . $conexion->error;
}
?>