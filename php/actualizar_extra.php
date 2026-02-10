<?php
include 'conexion.php';

// Validamos que los datos existan
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$concepto = isset($_POST['concepto']) ? trim($_POST['concepto']) : '';
$monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0.0;

if ($id > 0 && !empty($concepto)) {
    $sql = "UPDATE ingresos_b SET concepto = ?, monto = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sdi", $concepto, $monto, $id);

    if ($stmt->execute()) {
        echo "✅ Ingreso extra actualizado.";
    } else {
        echo "❌ Error al actualizar: " . $conexion->error;
    }
    $stmt->close();
} else {
    echo "❌ Faltan datos obligatorios.";
}
$conexion->close();
?>