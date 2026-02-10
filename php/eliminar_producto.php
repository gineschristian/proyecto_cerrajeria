<?php
include 'conexion.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // 1. (Opcional) Primero podrías buscar el nombre de la imagen para borrarla del servidor
    $query_img = "SELECT imagen FROM productos WHERE id = ?";
    $stmt_img = $conexion->prepare($query_img);
    $stmt_img->bind_param("i", $id);
    $stmt_img->execute();
    $resultado = $stmt_img->get_result();
    if ($fila = $resultado->fetch_assoc()) {
        $ruta_foto = "../img/productos/" . $fila['imagen'];
        if ($fila['imagen'] != "default.jpg" && file_exists($ruta_foto)) {
            unlink($ruta_foto); // Borra el archivo físico de la carpeta
        }
    }

    // 2. Borrar el registro de la base de datos
    $sql = "DELETE FROM productos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "✅ Producto eliminado correctamente";
        } else {
            echo "❌ Error al eliminar: " . $conexion->error;
        }
        $stmt->close();
    }
}
$conexion->close();
?>