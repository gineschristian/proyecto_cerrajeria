<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['rol']) || strtolower(trim($_SESSION['rol'])) !== 'admin') { exit; }

$nombre = $_POST['nombre'] ?? '';
$cif = $_POST['cif'] ?? '';
$telefono = $_POST['telefono'] ?? '';

if (!empty($nombre)) {
    $nombre = mysqli_real_escape_string($conexion, $nombre);
    $sql = "INSERT INTO proveedores (nombre, cif, telefono) VALUES ('$nombre', '$cif', '$telefono')";
    mysqli_query($conexion, $sql);
    header("Location: ../admin/proveedores.php?msj=Proveedor guardado&t=" . time());
}
?>