<?php
include 'conexion.php';

$concepto = $_POST['concepto'] ?? '';
$monto = (float)($_POST['monto'] ?? 0);
$fecha = $_POST['fecha'] ?? date('Y-m-d');
$factura = (int)($_POST['factura'] ?? 0); // Capturamos el nuevo campo

if (!empty($concepto) && $monto > 0) {
    // Añadimos 'factura' a la consulta SQL
    $sql = "INSERT INTO gastos (fecha, concepto, monto, factura) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssdi", $fecha, $concepto, $monto, $factura);

    if ($stmt->execute()) {
        echo "✅ Gasto registrado correctamente.";
    } else {
        echo "❌ Error al guardar: " . $conexion->error;
    }
    $stmt->close();
} else {
    echo "❌ Rellena todos los campos.";
}
$conexion->close();
?>