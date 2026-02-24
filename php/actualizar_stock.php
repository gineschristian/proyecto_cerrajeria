<?php
include 'conexion.php';

// 1. Recogemos los datos básicos del producto
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre = $_POST['nombre'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$cant_almacen = isset($_POST['cant_almacen']) ? (int)$_POST['cant_almacen'] : 0;

// 2. Recogemos el array de stock de los usuarios (enviado desde el JS)
// Formato esperado: [id_usuario => cantidad]
$stock_usuarios = isset($_POST['stock_usuarios']) ? $_POST['stock_usuarios'] : [];

if ($id > 0 && !empty($nombre)) {
    
    // Iniciamos una transacción para asegurar que todo se guarde bien o nada se guarde
    $conexion->begin_transaction();

    try {
        // A. Actualizamos los datos básicos en la tabla 'productos'
        // (Ya no tocamos cant_jefe ni cant_empleado aquí)
        $sql_prod = "UPDATE productos SET nombre = ?, categoria = ?, cant_almacen = ? WHERE id = ?";
        $stmt_prod = $conexion->prepare($sql_prod);
        $stmt_prod->bind_param("ssii", $nombre, $categoria, $cant_almacen, $id);
        $stmt_prod->execute();

        // B. Procesamos el stock de cada usuario/furgoneta
        foreach ($stock_usuarios as $id_usuario => $cantidad) {
            $id_usuario = (int)$id_usuario;
            $cantidad = (int)$cantidad;

            // Usamos REPLACE INTO o una lógica de DUPLICATE KEY 
            // Esto inserta si no existe, o actualiza si ya existe el registro para ese producto y usuario
            $sql_stock = "INSERT INTO stock_usuarios (id_producto, id_usuario, cantidad) 
                          VALUES (?, ?, ?) 
                          ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad)";
            
            $stmt_stock = $conexion->prepare($sql_stock);
            $stmt_stock->bind_param("iii", $id, $id_usuario, $cantidad);
            $stmt_stock->execute();
        }

        // Si todo ha ido bien, confirmamos los cambios
        $conexion->commit();
        echo "✅ Stock actualizado correctamente en todas las ubicaciones.";

    } catch (Exception $e) {
        // Si hay algún error, deshacemos los cambios
        $conexion->rollback();
        echo "❌ Error al actualizar: " . $e->getMessage();
    }

} else {
    echo "❌ Datos insuficientes para la actualización.";
}

$conexion->close();
?>