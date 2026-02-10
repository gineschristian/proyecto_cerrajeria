<?php
include 'conexion.php';

// Recogemos los datos enviados por el modal
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$cliente = $_POST['cliente'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$precio = isset($_POST['precio_total']) ? (float)$_POST['precio_total'] : 0.0;
$estado = $_POST['estado'] ?? 'Pendiente';

if ($id > 0 && !empty($cliente)) {
    // La consulta ahora incluye 'estado' porque ya existe en SQL
    $sql = "UPDATE trabajos SET cliente = ?, descripcion = ?, precio_total = ?, estado = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssdsi", $cliente, $descripcion, $precio, $estado, $id);

    if ($stmt->execute()) {
        echo "✅ Trabajo actualizado correctamente.";
    } else {
        echo "❌ Error al actualizar: " . $conexion->error;
    }
    $stmt->close();
} else {
    echo "❌ Datos incompletos.";
}

$conexion->close();
?>