<?php
include 'conexion.php';
$id = $_POST['id'];
$desc = $_POST['descripcion'];
$precio = $_POST['precio'];

$sql = "UPDATE trabajos SET descripcion = ?, precio_total = ? WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sdi", $desc, $precio, $id);

if($stmt->execute()) {
    echo "✅ Trabajo actualizado.";
} else {
    echo "❌ Error.";
}
?>