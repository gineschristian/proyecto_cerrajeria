<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    die("❌ Error: Sesión no iniciada.");
}

$usuario_id = $_SESSION['usuario_id'];

// NUEVOS CAMPOS RECIBIDOS
$cliente = $_POST['cliente']; // Dirección
$telefono = $_POST['telefono'];
$localidad = $_POST['localidad'];
$nombre_cliente = !empty($_POST['nombre_cliente']) ? $_POST['nombre_cliente'] : null;

$descripcion = $_POST['description']; 
$precio = $_POST['precio_total'];
$factura = isset($_POST['factura']) ? (int)$_POST['factura'] : 0;

// Recibimos los arrays de materiales y procedencia
$productos = isset($_POST['productos']) ? $_POST['productos'] : [];
$cantidades = isset($_POST['cantidades']) ? $_POST['cantidades'] : [];
$origenes = isset($_POST['origenes']) ? $_POST['origenes'] : [];

$conexion->begin_transaction();

try {
    // 1. Insertar el trabajo base con los nuevos campos (telefono, localidad, nombre_cliente)
    // El orden de las "s" en bind_param debe coincidir: cliente(s), telefono(s), localidad(s), nombre_cliente(s), descripcion(s), precio(d), factura(i), usuario_id(i)
    $sqlTrabajo = "INSERT INTO trabajos (cliente, telefono, localidad, nombre_cliente, descripcion, precio_total, factura, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtT = $conexion->prepare($sqlTrabajo);
    $stmtT->bind_param("sssssdii", $cliente, $telefono, $localidad, $nombre_cliente, $descripcion, $precio, $factura, $usuario_id);
    $stmtT->execute();
    
    $trabajo_id = $conexion->insert_id;

    // 2. Procesar la lista de materiales
    for ($i = 0; $i < count($productos); $i++) {
        $p_id = $productos[$i];
        $cant = (int)$cantidades[$i];
        $origen = isset($origenes[$i]) ? $origenes[$i] : 'furgoneta'; 

        if (!empty($p_id) && $cant > 0) {
            
            if ($origen === 'taller') {
                // A. Descontar del Almacén Central
                $sqlStock = "UPDATE productos SET cant_almacen = cant_almacen - ? WHERE id = ? AND cant_almacen >= ?";
                $stmtS = $conexion->prepare($sqlStock);
                $stmtS->bind_param("iii", $cant, $p_id, $cant);
            } else {
                // B. Descontar de la Furgoneta
                $sqlStock = "UPDATE stock_usuarios SET cantidad = cantidad - ? WHERE id_producto = ? AND id_usuario = ? AND cantidad >= ?";
                $stmtS = $conexion->prepare($sqlStock);
                $stmtS->bind_param("iiii", $cant, $p_id, $usuario_id, $cant);
            }

            $stmtS->execute();
            
            if ($stmtS->affected_rows == 0) {
                $msg_error = ($origen === 'taller') ? "en el Taller" : "en tu furgoneta";
                throw new Exception("Stock insuficiente para uno de los productos $msg_error.");
            }

            // C. Registrar la relación en trabajo_materiales
            $sqlRelacion = "INSERT INTO trabajo_materiales (trabajo_id, producto_id, cantidad, origen) VALUES (?, ?, ?, ?)";
            $stmtR = $conexion->prepare($sqlRelacion);
            $stmtR->bind_param("iiis", $trabajo_id, $p_id, $cant, $origen);
            $stmtR->execute();
        }
    }

    // 3. Registro en ingresos_b (Caja B) si no hay factura
    if ($factura === 0) {
        // Usamos el nombre del cliente si existe, si no, la dirección para el concepto
        $identificador = $nombre_cliente ? $nombre_cliente : $cliente;
        $conceptoB = "Trabajo: " . $identificador;
        
        $sqlExtra = "INSERT INTO ingresos_b (concepto, monto, trabajo_id) VALUES (?, ?, ?)";
        $stmtE = $conexion->prepare($sqlExtra);
        $stmtE->bind_param("sdi", $conceptoB, $precio, $trabajo_id);
        $stmtE->execute();
    }

    $conexion->commit();
    echo "success";

} catch (Exception $e) {
    $conexion->rollback();
    echo "❌ Error: " . $e->getMessage();
}

$conexion->close();
?>