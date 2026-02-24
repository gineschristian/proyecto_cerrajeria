<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Sesión no iniciada.");
}

$id_trabajo = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$id_usuario_actual = $_SESSION['usuario_id'];

if ($id_trabajo > 0) {
    $conexion->begin_transaction();

    try {
        // 1. Obtener los materiales y quién hizo el trabajo
        // Necesitamos el usuario_id del trabajo original para devolverle el stock a SU furgoneta
        $sql_info = "SELECT t.usuario_id, tm.producto_id, tm.cantidad, tm.origen 
                     FROM trabajos t
                     JOIN trabajo_materiales tm ON t.id = tm.trabajo_id
                     WHERE t.id = ?";
        
        $stmt_info = $conexion->prepare($sql_info);
        $stmt_info->bind_param("i", $id_trabajo);
        $stmt_info->execute();
        $resultado = $stmt_info->get_result();

        while ($fila = $resultado->fetch_assoc()) {
            $id_prod = $fila['producto_id'];
            $cant = $fila['cantidad'];
            $origen = $fila['origen'];
            $id_usuario_trabajo = $fila['usuario_id'];

            if ($origen === 'taller') {
                // A. Devolver al Almacén Central (Tabla productos)
                $sql_upd = "UPDATE productos SET cant_almacen = cant_almacen + ? WHERE id = ?";
                $stmt_upd = $conexion->prepare($sql_upd);
                $stmt_upd->bind_param("di", $cant, $id_prod);
            } else {
                // B. Devolver a la Furgoneta (Tabla stock_usuarios)
                $sql_upd = "UPDATE stock_usuarios SET cantidad = cantidad + ? WHERE id_producto = ? AND id_usuario = ?";
                $stmt_upd = $conexion->prepare($sql_upd);
                $stmt_upd->bind_param("dii", $cant, $id_prod, $id_usuario_trabajo);
            }
            $stmt_upd->execute();
        }

        // 2. Borrar de ingresos_b si existía registro
        $stmt_del_b = $conexion->prepare("DELETE FROM ingresos_b WHERE trabajo_id = ?");
        $stmt_del_b->bind_param("i", $id_trabajo);
        $stmt_del_b->execute();

        // 3. Borrar materiales vinculados
        $stmt_del_mat = $conexion->prepare("DELETE FROM trabajo_materiales WHERE trabajo_id = ?");
        $stmt_del_mat->bind_param("i", $id_trabajo);
        $stmt_del_mat->execute();

        // 4. Borrar el trabajo
        $stmt_del_trabajo = $conexion->prepare("DELETE FROM trabajos WHERE id = ?");
        $stmt_del_trabajo->bind_param("i", $id_trabajo);
        $stmt_del_trabajo->execute();

        $conexion->commit();
        echo "success";

    } catch (Exception $e) {
        $conexion->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "ID no válido.";
}
$conexion->close();
?>