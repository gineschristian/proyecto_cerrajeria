<?php
include 'conexion.php';

// Recogemos el ID del registro y los nuevos valores
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$concepto = $_POST['concepto'] ?? ''; // En la tabla trabajos, esto es el 'cliente'
$monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0.0;

if ($id > 0) {
    // IMPORTANTE: Actualizamos la tabla 'trabajos'
    // 'concepto' en el modal corresponde a 'cliente' en la base de datos
    $sql = "UPDATE trabajos SET cliente = ?, precio_total = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sdi", $concepto, $monto, $id);

    if ($stmt->execute()) {
        echo "✅ Ingreso actualizado correctamente en el historial de trabajos.";
    } else {
        echo "❌ Error al actualizar: " . $conexion->error;
    }
    $stmt->close();
} else {
    echo "❌ ID de ingreso no válido.";
}

$conexion->close();
?>