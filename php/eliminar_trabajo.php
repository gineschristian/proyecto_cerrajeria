<?php
include 'conexion.php';

$id = $_POST['id'];

// 1. Antes de borrar, consultamos qué material se usó y cuánto
$query = "SELECT producto_usado, cantidad_usada FROM trabajos WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$trabajo = $res->fetch_assoc();

$conexion->begin_transaction();

try {
    // 2. Si hubo material usado, lo devolvemos al stock
    if ($trabajo['producto_usado'] && $trabajo['cantidad_usada'] > 0) {
        $updateStock = "UPDATE productos SET cantidad = cantidad + ? WHERE id = ?";
        $stmtS = $conexion->prepare($updateStock);
        $stmtS->bind_param("ii", $trabajo['cantidad_usada'], $trabajo['producto_usado']);
        $stmtS->execute();
    }

    // 3. Borramos el trabajo
    $delete = "DELETE FROM trabajos WHERE id = ?";
    $stmtD = $conexion->prepare($delete);
    $stmtD->bind_param("i", $id);
    $stmtD->execute();

    $conexion->commit();
    echo "✅ Trabajo eliminado y stock devuelto.";
} catch (Exception $e) {
    $conexion->rollback();
    echo "❌ Error al eliminar: " . $e->getMessage();
}
?>