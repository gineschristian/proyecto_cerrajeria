<?php
include 'conexion.php';

// Recogemos los datos enviados por el formulario (FormData)
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre = $_POST['nombre'] ?? '';
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
$categoria = $_POST['categoria'] ?? '';

if ($id > 0 && !empty($nombre)) {
    // Preparamos la consulta para actualizar nombre, cantidad y categoría
    $sql = "UPDATE productos SET nombre = ?, cantidad = ?, categoria = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sisi", $nombre, $cantidad, $categoria, $id);

    if ($stmt->execute()) {
        echo "✅ Stock actualizado correctamente.";
    } else {
        echo "❌ Error al actualizar la base de datos: " . $conexion->error;
    }
    
    $stmt->close();
} else {
    echo "❌ Datos insuficientes para la actualización.";
}

$conexion->close();
?>