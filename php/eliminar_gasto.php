<?php
include 'conexion.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM gastos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "✅ Gasto eliminado.";
    } else {
        echo "❌ Error al eliminar.";
    }
    $stmt->close();
}

$conexion->close();
?>