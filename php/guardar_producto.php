<?php
include 'conexion.php';

$nombre = $_POST['nombre'];
$cantidad_inicial = $_POST['cantidad']; // Esta será la cantidad que va al almacén
$categoria = $_POST['categoria']; 
$nombre_imagen = "default.jpg"; 

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
    $directorio = "../img/productos/";
    if (!file_exists($directorio)) { mkdir($directorio, 0777, true); }

    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $nombre_imagen = "prod_" . time() . "." . $extension;
    move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio . $nombre_imagen);
}

// Modificamos la consulta para apuntar a cant_almacen 
// cant_jefe y cant_empleado se quedan en 0 por defecto según la estructura de la tabla
$sql = "INSERT INTO productos (nombre, categoria, cant_almacen, cant_jefe, cant_empleado, imagen) VALUES (?, ?, ?, 0, 0, ?)";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    // "ssis" -> nombre(s), categoria(s), cant_almacen(i), imagen(s)
    $stmt->bind_param("ssis", $nombre, $categoria, $cantidad_inicial, $nombre_imagen);
    
    if ($stmt->execute()) {
        echo "✅ Producto guardado correctamente en el Taller";
    } else {
        echo "❌ Error al ejecutar: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "❌ Error en la preparación: " . $conexion->error;
}

$conexion->close();
?>