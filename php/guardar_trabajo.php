<?php
session_start(); // Iniciamos sesión para saber quién es el empleado
include 'conexion.php';

// Verificamos que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    die("❌ Error: Sesión no iniciada.");
}

$usuario_id = $_SESSION['usuario_id']; // ID del empleado o admin actual
$cliente = $_POST['cliente'];
$descripcion = $_POST['descripcion'];
$producto_id = !empty($_POST['producto_id']) ? $_POST['producto_id'] : null;
$cantidad_gastada = (int)$_POST['cantidad_gastada'];
$precio = $_POST['precio_total'];
$factura = isset($_POST['factura']) ? (int)$_POST['factura'] : 0;

$conexion->begin_transaction();

try {
    // 1. Insertar el trabajo (Añadimos la columna usuario_id para saber quién lo hizo)
    // Asegúrate de tener la columna 'usuario_id' en tu tabla 'trabajos'
    $sqlTrabajo = "INSERT INTO trabajos (cliente, descripcion, producto_usado, cantidad_usada, precio_total, factura, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmtT = $conexion->prepare($sqlTrabajo);
    $stmtT->bind_param("ssiidii", $cliente, $descripcion, $producto_id, $cantidad_gastada, $precio, $factura, $usuario_id);
    $stmtT->execute();
    
    $trabajo_id = $conexion->insert_id;

    // 2. Si se usó un producto, descontarlo del stock
    if (!empty($producto_id) && $cantidad_gastada > 0) {
        // Solo descuenta si hay stock suficiente (AND cantidad >= ?)
        $sqlStock = "UPDATE productos SET cantidad = cantidad - ? WHERE id = ? AND cantidad >= ?";
        $stmtS = $conexion->prepare($sqlStock);
        $stmtS->bind_param("iii", $cantidad_gastada, $producto_id, $cantidad_gastada);
        $stmtS->execute();
        
        if ($stmtS->affected_rows == 0) {
            throw new Exception("No hay suficiente stock de este producto.");
        }
    }

    // 3. Si NO lleva factura, registrar en EXTRAS
    if ($factura === 0) {
        $conceptoB = "Trabajo: " . $cliente;
        $sqlExtra = "INSERT INTO ingresos_b (concepto, monto, trabajo_id) VALUES (?, ?, ?)";
        $stmtE = $conexion->prepare($sqlExtra);
        $stmtE->bind_param("sdi", $conceptoB, $precio, $trabajo_id);
        $stmtE->execute();
    }

    $conexion->commit();
    echo "✅ Trabajo registrado por " . $_SESSION['nombre'] . ", stock actualizado" . ($factura === 0 ? " y enviado a Extras." : ".");

} catch (Exception $e) {
    $conexion->rollback();
    echo "❌ Error: " . $e->getMessage();
}

$conexion->close();
?>