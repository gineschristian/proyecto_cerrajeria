<?php
include 'conexion.php';

// Recogemos los datos del formulario (incluyendo el nuevo campo proveedor)
$proveedor   = $_POST['proveedor'] ?? 'Otros';
$concepto    = $_POST['concepto'] ?? '';
$categoria   = $_POST['categoria'] ?? '';
$monto       = $_POST['monto'] ?? 0;
$con_factura = isset($_POST['con_factura']) ? (int)$_POST['con_factura'] : 0;

// Validamos que los campos esenciales no estén vacíos
if (!empty($proveedor) && !empty($concepto) && !empty($monto)) {
    
    // 1. Preparamos la consulta incluyendo la columna 'proveedor'
    // Asegúrate de haber ejecutado: ALTER TABLE gastos ADD COLUMN proveedor VARCHAR(100);
    $sql = "INSERT INTO gastos (proveedor, concepto, categoria, monto, con_factura, fecha) VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conexion->prepare($sql);
    
    // "ssdi" cambia a "sssdi" porque añadimos un String (proveedor) al principio
    $stmt->bind_param("sssdi", $proveedor, $concepto, $categoria, $monto, $con_factura);

    if ($stmt->execute()) {
        echo "✅ Gasto registrado correctamente.";
    } else {
        echo "❌ Error al guardar el gasto: " . $conexion->error;
    }
    
    $stmt->close();
} else {
    echo "❌ Por favor, rellena todos los campos obligatorios (Proveedor, Concepto y Monto).";
}

$conexion->close();
?>