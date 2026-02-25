<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Seguridad: Solo admin
if (!isset($_SESSION['usuario_id']) || strtolower(trim($_SESSION['rol'])) !== 'admin') {
    echo "❌ Acceso denegado.";
    exit();
}

include 'conexion.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Opcional: Podrías verificar si hay productos usando esta categoría antes de borrar
    // Pero por ahora lo haremos directo
    $sql = "DELETE FROM categorias_stock WHERE id = $id";
    
    if (mysqli_query($conexion, $sql)) {
        echo "✅ Categoría eliminada.";
    } else {
        echo "❌ Error al eliminar: " . mysqli_error($conexion);
    }
}

mysqli_close($conexion);
?>