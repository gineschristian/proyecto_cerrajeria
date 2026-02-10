<?php
include 'conexion.php';

$nombre = $_POST['nombre'];
$cantidad = $_POST['cantidad'];
$categoria = $_POST['categoria']; // Capturamos la categoría
$nombre_imagen = "default.jpg"; 

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
    $directorio = "../img/productos/";
    if (!file_exists($directorio)) { mkdir($directorio, 0777, true); }

    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $nombre_imagen = "prod_" . time() . "." . $extension;
    move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio . $nombre_imagen);
}

// Preparamos la consulta con los 4 campos
$sql = "INSERT INTO productos (nombre, categoria, cantidad, imagen) VALUES (?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    // "ssis" significa: string, string, integer, string
    $stmt->bind_param("ssis", $nombre, $categoria, $cantidad, $nombre_imagen);
    
    if ($stmt->execute()) {
        echo "✅ Producto guardado correctamente";
    } else {
        echo "❌ Error al ejecutar: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "❌ Error en la preparación: " . $conexion->error;
}

$conexion->close();
?>