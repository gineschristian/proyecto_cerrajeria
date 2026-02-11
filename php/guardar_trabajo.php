<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    die("❌ Error: Sesión no iniciada.");
}

$usuario_id = $_SESSION['usuario_id'];
$cliente = $_POST['cliente'];
$descripcion = $_POST['description']; // Usamos 'description' que es el name del textarea en el HTML
$precio = $_POST['precio_total'];
$factura = isset($_POST['factura']) ? (int)$_POST['factura'] : 0;

// Recibimos los arrays de materiales
$productos = isset($_POST['productos']) ? $_POST['productos'] : [];
$cantidades = isset($_POST['cantidades']) ? $_POST['cantidades'] : [];

$conexion->begin_transaction();

try {
    // 1. Insertar el trabajo base
    // Nota: Eliminamos producto_usado y cantidad_usada de aquí porque ahora van en su propia tabla
    $sqlTrabajo = "INSERT INTO trabajos (cliente, descripcion, precio_total, factura, usuario_id) VALUES (?, ?, ?, ?, ?)";
    $stmtT = $conexion->prepare($sqlTrabajo);
    $stmtT->bind_param("ssdii", $cliente, $descripcion, $precio, $factura, $usuario_id);
    $stmtT->execute();
    
    $trabajo_id = $conexion->insert_id;

    // 2. Procesar la lista de materiales (Bucle Multiselección)
    for ($i = 0; $i < count($productos); $i++) {
        $p_id = $productos[$i];
        $cant = (int)$cantidades[$i];

        if (!empty($p_id) && $cant > 0) {
            // A. Verificar stock y descontar
            $sqlStock = "UPDATE productos SET cantidad = cantidad - ? WHERE id = ? AND cantidad >= ?";
            $stmtS = $conexion->prepare($sqlStock);
            $stmtS->bind_param("iii", $cant, $p_id, $cant);
            $stmtS->execute();
            
            if ($stmtS->affected_rows == 0) {
                // Si falla uno, se cancela todo el guardado para no descuadrar inventario
                throw new Exception("Stock insuficiente para uno de los productos seleccionados.");
            }

            // B. Registrar la relación en la tabla intermedia (IMPORTANTE)
            // Asegúrate de haber creado la tabla 'trabajo_materiales' antes
            $sqlRelacion = "INSERT INTO trabajo_materiales (trabajo_id, producto_id, cantidad) VALUES (?, ?, ?)";
            $stmtR = $conexion->prepare($sqlRelacion);
            $stmtR->bind_param("iii", $trabajo_id, $p_id, $cant);
            $stmtR->execute();
        }
    }

    // 3. Si NO lleva factura, registrar en ingresos_b
    if ($factura === 0) {
        $conceptoB = "Trabajo: " . $cliente;
        $sqlExtra = "INSERT INTO ingresos_b (concepto, monto, trabajo_id) VALUES (?, ?, ?)";
        $stmtE = $conexion->prepare($sqlExtra);
        $stmtE->bind_param("sdi", $conceptoB, $precio, $trabajo_id);
        $stmtE->execute();
    }

    $conexion->commit();
    echo "success"; // Enviamos success para que el JS sepa que todo salió bien

} catch (Exception $e) {
    $conexion->rollback();
    echo "❌ Error: " . $e->getMessage();
}

$conexion->close();
?>