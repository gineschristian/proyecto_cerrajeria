<?php
include 'conexion.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0) {
    // Usamos una consulta preparada por seguridad
    $sql = "DELETE FROM ingresos_b WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "✅ Ingreso extra eliminado correctamente.";
    } else {
        echo "❌ Error al eliminar: " . $conexion->error;
    }
    $stmt->close();
} else {
    echo "❌ ID no válido.";
}

$conexion->close();
?>