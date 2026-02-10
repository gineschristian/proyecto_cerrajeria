<?php
session_start();
include 'conexion.php';

// Verificación de seguridad: solo admin puede borrar
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado.");
}

$id = $_POST['id'] ?? null;

if ($id) {
    // No permitir que un admin se borre a sí mismo
    if ($id == $_SESSION['usuario_id']) {
        die("No puedes borrar tu propia cuenta.");
    }

    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    $stmt->close();
}
?>