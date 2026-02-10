<?php
include 'conexion.php';

$concepto = $_POST['concepto'];
$categoria = $_POST['categoria'];
$monto = $_POST['monto'];
$con_factura = isset($_POST['con_factura']) ? (int)$_POST['con_factura'] : 0;

if (!empty($concepto) && !empty($monto)) {
    $sql = "INSERT INTO gastos (concepto, categoria, monto, con_factura, fecha) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssdi", $concepto, $categoria, $monto, $con_factura);

    if ($stmt->execute()) {
        echo "✅ Gasto registrado correctamente.";
    } else {
        echo "❌ Error al guardar el gasto: " . $conexion->error;
    }
    $stmt->close();
} else {
    echo "❌ Por favor, rellena todos los campos obligatorios.";
}

$conexion->close();
?>