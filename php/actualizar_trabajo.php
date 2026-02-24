<?php
include 'conexion.php';

// Los nombres de la izquierda deben coincidir con el atributo 'name' de tu HTML
$id             = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$telefono       = $_POST['telefono'] ?? '';
$localidad      = $_POST['localidad'] ?? '';
$nombre_cliente = $_POST['nombre_cliente'] ?? '';
$cliente        = $_POST['cliente'] ?? ''; // Dirección
$descripcion    = $_POST['description'] ?? ''; // Coincide con el 'name' del textarea
$precio         = isset($_POST['precio_total']) ? (float)$_POST['precio_total'] : 0.0;
$estado         = $_POST['estado'] ?? 'Pendiente';

if ($id > 0) {
    $sql = "UPDATE trabajos SET 
                telefono = ?, 
                localidad = ?, 
                nombre_cliente = ?, 
                cliente = ?, 
                descripcion = ?, 
                precio_total = ?,
                estado = ?
            WHERE id = ?";
            
    $stmt = $conexion->prepare($sql);
    
    // sssssdsi -> 5 strings, 1 decimal (double), 1 string, 1 entero (id)
    $stmt->bind_param("sssssdsi", 
        $telefono, 
        $localidad, 
        $nombre_cliente, 
        $cliente, 
        $descripcion, 
        $precio,
        $estado,
        $id
    );

    if ($stmt->execute()) {
        echo "success"; 
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "ID no válido";
}
$conexion->close();
?>