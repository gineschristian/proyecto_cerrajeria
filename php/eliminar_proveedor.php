<?php
include 'conexion.php';
session_start();

// 1. Verificamos seguridad (Solo Admin puede eliminar)
if (!isset($_SESSION['rol']) || strtolower(trim($_SESSION['rol'])) !== 'admin') {
    header("Location: ../index.html");
    exit();
}

// 2. Obtenemos el ID del proveedor a eliminar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 3. Ejecutamos la eliminación
    $sql = "DELETE FROM proveedores WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Éxito: Volvemos con mensaje positivo
        header("Location: ../admin/proveedores.php?msj=Proveedor eliminado correctamente&t=" . time());
    } else {
        // Error: Volvemos con mensaje de error
        header("Location: ../admin/proveedores.php?msj=Error al eliminar el proveedor&t=" . time());
    }
    
    $stmt->close();
} else {
    // Si no hay ID válido, simplemente volvemos
    header("Location: ../admin/proveedores.php");
}

$conexion->close();
?>