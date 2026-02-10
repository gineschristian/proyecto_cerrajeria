<?php
include 'conexion.php';

if(isset($_POST['id'], $_POST['nombre'], $_POST['categoria'], $_POST['cantidad'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $cantidad = $_POST['cantidad'];

    $sql = "UPDATE productos SET nombre = ?, categoria = ?, cantidad = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssii", $nombre, $categoria, $cantidad, $id);
        if ($stmt->execute()) {
            echo "✅ Producto actualizado correctamente";
        } else {
            echo "❌ Error al actualizar: " . $conexion->error;
        }
        $stmt->close();
    }
} else {
    echo "❌ Faltan datos en la solicitud";
}
$conexion->close();
?>