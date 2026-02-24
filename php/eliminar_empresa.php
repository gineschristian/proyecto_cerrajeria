<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['rol']) || strtolower(trim($_SESSION['rol'])) !== 'admin') {
    die("No autorizado");
}

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    $id = mysqli_real_escape_string($conexion, $id);
    $query = "DELETE FROM empresas WHERE id = '$id'";
    
    if (mysqli_query($conexion, $query)) {
    header("Location: ../admin/empresas.php?msj=Empresa eliminada&t=" . time());
    exit();
} else {
        echo "Error al eliminar";
    }
}
?>