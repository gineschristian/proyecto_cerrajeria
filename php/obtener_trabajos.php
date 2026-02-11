<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'conexion.php';

// Verificamos quién está pidiendo los datos
$usuario_id_sesion = $_SESSION['usuario_id'] ?? 0;
// Usamos strtolower y trim para evitar fallos de formato en la BD
$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');

$inicio = $_GET['inicio'] ?? '';
$fin = $_GET['fin'] ?? '';

// 1. CONSULTA MEJORADA
$query = "SELECT t.*, u.nombre as nombre_empleado,
          GROUP_CONCAT(CONCAT(p.nombre, ' (', tm.cantidad, ')') SEPARATOR '<br>') AS materiales_usados
          FROM trabajos t 
          LEFT JOIN usuarios u ON t.usuario_id = u.id
          LEFT JOIN trabajo_materiales tm ON t.id = tm.trabajo_id
          LEFT JOIN productos p ON tm.producto_id = p.id ";

$condiciones = [];

// Si NO es admin, solo puede ver sus propios trabajos
if (!$esAdmin) {
    $condiciones[] = "t.usuario_id = '$usuario_id_sesion'";
}

if (!empty($inicio) && !empty($fin)) {
    $inicioClean = mysqli_real_escape_string($conexion, $inicio);
    $finClean = mysqli_real_escape_string($conexion, $fin);
    $condiciones[] = "t.fecha BETWEEN '$inicioClean 00:00:00' AND '$finClean 23:59:59'";
}

if (count($condiciones) > 0) {
    $query .= " WHERE " . implode(" AND ", $condiciones);
}

$query .= " GROUP BY t.id ORDER BY t.fecha DESC";

$resultado = mysqli_query($conexion, $query);
$sumaTotal = 0;
$filasHTML = "";

if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $sumaTotal += $fila['precio_total'];
        
        // CORRECCIÓN PARA PHP 8.1+ (InfinityFree): Añadimos ?? "" a los campos que pueden ser NULL
        $datosJSON = htmlspecialchars(json_encode($fila, JSON_HEX_QUOT | JSON_HEX_APOS), ENT_QUOTES, 'UTF-8');
        $iconoFactura = ($fila['factura'] == 1) ? "<span style='color: #27ae60; font-weight:bold;'>📄 SÍ</span>" : "<span style='color: #888;'>❌ NO</span>";
        $materiales = !empty($fila['materiales_usados']) ? $fila['materiales_usados'] : '<small style="color:#bbb;">Solo mano obra</small>';

        // Nombre del empleado (con protección contra nulos)
        $nombreEmp = htmlspecialchars($fila['nombre_empleado'] ?? "Desconocido");
        $columnaEmpleado = $esAdmin ? "<br><small style='color:#3498db;'>👤 " . $nombreEmp . "</small>" : "";

        $filasHTML .= "<tr>
            <td data-label='Fecha'>" . date("d/m/Y", strtotime($fila['fecha'])) . "</td>
            <td data-label='Cliente'><strong>" . htmlspecialchars($fila['cliente'] ?? "") . "</strong> $columnaEmpleado</td>
            <td data-label='Descripción'>" . htmlspecialchars($fila['descripcion'] ?? "") . "</td>
            <td data-label='Factura' style='text-align:center;'>$iconoFactura</td>
            <td data-label='Materiales'>$materiales</td>
            <td data-label='Total' style='color: #27ae60; font-weight: bold;'>" . number_format($fila['precio_total'], 2) . "€</td>
            <td data-label='Acciones'>
                <div style='display: flex; gap: 10px; justify-content: center;'>
                    <button class='btn-header' style='background: #27ae60; padding: 10px; border:none; border-radius:5px; cursor:pointer;' onclick='abrirEditarTrabajo($datosJSON)'>✏️</button>
                    <button class='btn-header' style='background: #e74c3c; padding: 10px; border:none; border-radius:5px; cursor:pointer;' onclick='eliminarTrabajo({$fila['id']})'>🗑️</button>
                </div>
            </td>
        </tr>";
    }
} else {
    $filasHTML = "<tr><td colspan='7' style='text-align:center; padding: 30px; color: #7f8c8d;'>No hay trabajos registrados.</td></tr>";
}

echo $filasHTML;
echo "<script>if(document.getElementById('totalFacturado')) { document.getElementById('totalFacturado').innerText = '" . number_format($sumaTotal, 2) . "€'; }</script>";
$conexion->close();
?>