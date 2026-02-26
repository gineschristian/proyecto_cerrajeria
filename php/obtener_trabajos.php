<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'conexion.php';

$usuario_id_sesion = $_SESSION['usuario_id'] ?? 0;
$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');

$inicio = $_GET['inicio'] ?? '';
$fin = $_GET['fin'] ?? '';
$empleadoFiltro = $_GET['empleado'] ?? ''; 

$query = "SELECT t.*, u.nombre as nombre_empleado,
          GROUP_CONCAT(CONCAT(p.nombre, ' (', tm.cantidad, ')') SEPARATOR ', ') AS materiales_lista
          FROM trabajos t 
          LEFT JOIN usuarios u ON t.usuario_id = u.id
          LEFT JOIN trabajo_materiales tm ON t.id = tm.trabajo_id
          LEFT JOIN productos p ON tm.producto_id = p.id ";

$condiciones = [];

if (!$esAdmin) {
    $condiciones[] = "t.usuario_id = '$usuario_id_sesion'";
}

if (!empty($inicio) && !empty($fin)) {
    $inicioClean = mysqli_real_escape_string($conexion, $inicio);
    $finClean = mysqli_real_escape_string($conexion, $fin);
    $condiciones[] = "t.fecha BETWEEN '$inicioClean 00:00:00' AND '$finClean 23:59:59'";
}

if (!empty($empleadoFiltro) && $esAdmin) {
    $empleadoClean = mysqli_real_escape_string($conexion, $empleadoFiltro);
    $condiciones[] = "u.nombre = '$empleadoClean'";
}

if (count($condiciones) > 0) {
    $query .= " WHERE " . implode(" AND ", $condiciones);
}

$query .= " GROUP BY t.id ORDER BY t.fecha DESC";

$resultado = mysqli_query($conexion, $query);
$sumaTotal = 0;
$filasHTML = "";

$totalColumnas = $esAdmin ? 6 : 5;

if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $sumaTotal += (float)$fila['precio_total'];
        
        $datosJSON = htmlspecialchars(json_encode($fila, JSON_HEX_QUOT | JSON_HEX_APOS), ENT_QUOTES, 'UTF-8');
        
        // --- CORRECCIÓN: Generamos la fecha limpia para el atributo data ---
        $fechaLimpiaParaJS = date("d/m/Y", strtotime($fila['fecha']));
        
        $iconoFactura = ($fila['factura'] == 1) 
            ? "<div style='color: #27ae60; font-weight: bold; font-size: 0.75rem; border: 1.5px solid #27ae60; padding: 2px 6px; border-radius: 4px; display: inline-block; background: #ebf9f1;'>SÍ</div>" 
            : "<div style='color: #bdc3c7; font-weight: bold; font-size: 0.75rem; border: 1.5px solid #bdc3c7; padding: 2px 4px; border-radius: 4px; display: inline-block;'>NO</div>";

        $htmlInfo = "<div style='display: flex; flex-direction: column; gap: 3px;'>";
            if(!empty($fila['nombre_cliente'])){
                $htmlInfo .= "<div style='font-weight: 700; color: #2c3e50; font-size: 0.95rem;'>" . strtoupper(htmlspecialchars($fila['nombre_cliente'])) . "</div>";
            }
            $htmlInfo .= "<div style='color: #2980b9; font-weight: 600; font-size: 0.9rem;'>📍 " . htmlspecialchars($fila['cliente'] ?? "") . "</div>";
            $htmlInfo .= "<div style='font-size: 0.8rem; color: #7f8c8d;'>📞 " . htmlspecialchars($fila['telefono'] ?? "---") . " | 🏙️ " . htmlspecialchars($fila['localidad'] ?? "---") . "</div>";
            
            if(!empty($fila['descripcion'])){
                $htmlInfo .= "<div style='background: #f9f9f9; border-left: 3px solid #3498db; padding: 4px 8px; font-size: 0.8rem; color: #555; margin-top: 4px; border-radius: 0 4px 4px 0; font-style: italic;'>" . htmlspecialchars($fila['descripcion']) . "</div>";
            }
        $htmlInfo .= "</div>";

        // --- CORRECCIÓN: Se añade el atributo data-fec con la fecha estandarizada ---
        $filasHTML .= "<tr>
            <td class='col-fecha' data-fec='{$fechaLimpiaParaJS}' style='vertical-align: middle; font-size: 0.85rem;'>
                <span style='display:block; font-weight:800;'>" . date("d/m", strtotime($fila['fecha'])) . "</span>
                <small style='color:#95a5a6;'>" . date("Y", strtotime($fila['fecha'])) . "</small>
            </td>
            <td class='col-info' style='border-left: 1px solid #f1f1f1;'>$htmlInfo</td>";

        if ($esAdmin) {
            $nombreOp = htmlspecialchars($fila['nombre_empleado'] ?? '---');
            $filasHTML .= "<td class='col-operario' style='vertical-align: middle;'>
                <div style='background: #e8f4fd; padding: 5px 10px; border-radius: 20px; display: inline-block; font-weight: 600; color: #2980b9; font-size: 0.8rem; border: 1px solid #d1e9f9;'>
                    👤 " . strtoupper($nombreOp) . "
                </div>
            </td>";
        }

        $filasHTML .= "<td class='col-factura'>$iconoFactura</td>
            
            <td class='col-total' data-precio='{$fila['precio_total']}' style='vertical-align: middle; font-weight: 800; color: #2ecc71; font-size: 1.05rem;'>
             " . number_format($fila['precio_total'], 2, ',', '.') . "€
            </td>
            
            <td class='col-accion' style='vertical-align: middle;'>
                <div style='display: flex; gap: 6px; justify-content: center;'>
                    <button type='button' title='Editar' style='background: #3498db; color: white; border:none; width: 32px; height: 32px; border-radius: 6px; cursor:pointer; display: flex; align-items: center; justify-content: center;' onclick='abrirEditarTrabajo($datosJSON)'>✏️</button>
                    <button type='button' title='Eliminar' style='background: #e74c3c; color: white; border:none; width: 32px; height: 32px; border-radius: 6px; cursor:pointer; display: flex; align-items: center; justify-content: center;' onclick='eliminarTrabajo({$fila['id']})'>🗑️</button>
                </div>
            </td>
        </tr>";
    }
} else {
    $filasHTML = "<tr><td colspan='$totalColumnas' style='text-align:center; padding: 40px; color: #95a5a6;'>No se encontraron trabajos con los filtros seleccionados.</td></tr>";
}

echo $filasHTML;

echo "<script>
    if(document.getElementById('totalFacturado')) { 
        document.getElementById('totalFacturado').innerText = '" . number_format($sumaTotal, 2, ',', '.') . "€'; 
    }
</script>";

$conexion->close();
?>