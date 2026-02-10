<?php
include 'conexion.php';

// Recogemos datos
$concepto = $_POST['concepto'] ?? '';
$monto = (float)($_POST['monto'] ?? 0);
$fecha = date('Y-m-d'); // Solo fecha o Y-m-d H:i:s según tu tabla

if (!empty($concepto) && $monto > 0) {
    // Intentamos el insert
    // Nota: Asegúrate de que los nombres de las columnas (fecha, concepto, monto, origen) 
    // sean exactamente los mismos que tienes en phpMyAdmin
    $sql = "INSERT INTO ingresos_b (fecha, concepto, monto, origen) VALUES (?, ?, ?, 'Manual')";
    $stmt = $conexion->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssd", $fecha, $concepto, $monto);
        
        if ($stmt->execute()) {
            echo "✅ Ingreso extra guardado correctamente.";
        } else {
            echo "❌ Error al ejecutar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "❌ Error en la preparación: " . $conexion->error;
    }
} else {
    echo "❌ Rellena todos los campos correctamente.";
}

$conexion->close();
?>