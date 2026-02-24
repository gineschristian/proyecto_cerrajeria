<?php
include 'conexion.php';
session_start();

// Verificamos permisos
if (!isset($_SESSION['rol']) || strtolower(trim($_SESSION['rol'])) !== 'admin') {
    die("No tienes permisos.");
}

$nombre   = $_POST['nombre']   ?? '';
$cif      = $_POST['cif']      ?? '';
$telefono = $_POST['telefono'] ?? '';

if (!empty($nombre)) {
    $nombre   = mysqli_real_escape_string($conexion, $nombre);
    $cif      = mysqli_real_escape_string($conexion, $cif);
    $telefono = mysqli_real_escape_string($conexion, $telefono);

    $sql = "INSERT INTO empresas (nombre, cif, telefono) VALUES ('$nombre', '$cif', '$telefono')";
    
   if (mysqli_query($conexion, $sql)) {
    // La ruta correcta para volver es entrar en la carpeta 'admin'
    header("Location: ../admin/empresas.php?msj=Empresa guardada correctamente&t=" . time());
    exit();
}else {
        echo "Error: " . mysqli_error($conexion);
    }
} else {
    echo "El nombre es obligatorio.";
}
?>