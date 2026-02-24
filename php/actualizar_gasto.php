<?php
include 'conexion.php';

// Recogemos los datos del formulario de edición
$id          = $_POST['id'] ?? '';
$proveedor   = $_POST['proveedor'] ?? 'Otros';
$concepto    = $_POST['concepto'] ?? '';
$categoria   = $_POST['categoria'] ?? '';
$monto       = (float)($_POST['monto'] ?? 0);
$con_factura = (int)($_POST['con_factura'] ?? 0);

if (!empty($id) && !empty($concepto) && $monto > 0) {
    
    // Usamos UPDATE en lugar de INSERT para modificar el registro existente
    $sql = "UPDATE gastos SET 
                proveedor = ?, 
                concepto = ?, 
                categoria = ?, 
                monto = ?, 
                con_factura = ? 
            WHERE id = ?";
            
    $stmt = $conexion->prepare($sql);
    
    // "sssdii" -> s (proveedor), s (concepto), s (categoria), d (monto), i (factura), i (id)
    $stmt->bind_param("sssdii", $proveedor, $concepto, $categoria, $monto, $con_factura, $id);

    if ($stmt->execute()) {
        echo "✅ Gasto actualizado correctamente.";
    } else {
        echo "❌ Error al actualizar: " . $conexion->error;
    }
    
    $stmt->close();
} else {
    echo "❌ Faltan datos necesarios para la actualización.";
}

$conexion->close();
?>