<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Seguridad Admin
if (!isset($_SESSION['usuario_id']) || strtolower(trim($_SESSION['rol'])) !== 'admin') {
    die("❌ Acceso denegado.");
}

include 'conexion.php';

if (isset($_POST['id']) && isset($_POST['nombre'])) {
    $id = intval($_POST['id']);
    $nuevoNombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    
    // 1. Opcional: ver si el nombre nuevo ya existe en otra categoría
    $check = mysqli_query($conexion, "SELECT id FROM categorias_stock WHERE nombre = '$nuevoNombre' AND id != $id");
    if (mysqli_num_rows($check) > 0) {
        die("⚠️ Ya existe otra categoría con ese nombre.");
    }

    // 2. Actualizar el nombre
    $sql = "UPDATE categorias_stock SET nombre = '$nuevoNombre' WHERE id = $id";
    
    if (mysqli_query($conexion, $sql)) {
        echo "✅ Categoría actualizada.";
    } else {
        echo "❌ Error al actualizar: " . mysqli_error($conexion);
    }
}

mysqli_close($conexion);
?>