<?php
include 'conexion.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$cliente = $_POST['cliente'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$precio = isset($_POST['precio_total']) ? (float)$_POST['precio_total'] : 0.0;
$estado = $_POST['estado'] ?? 'Pendiente';

if ($id > 0 && !empty($cliente)) {
    // Usamos sentencias preparadas para mayor seguridad
    $sql = "UPDATE trabajos SET cliente = ?, descripcion = ?, precio_total = ?, estado = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    
    // "ssdsi" significa: string, string, double (decimal), string, integer
    $stmt->bind_param("ssdsi", $cliente, $descripcion, $precio, $estado, $id);

    if ($stmt->execute()) {
        echo "success"; // IMPORTANTE: Enviamos solo 'success' para el JS
    } else {
        echo "Error: " . $conexion->error;
    }
    $stmt->close();
} else {
    echo "Datos incompletos";
}

$conexion->close();
?>