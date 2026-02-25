<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'conexion.php';

// Capturamos la empresa y las fechas del filtro
$nombre_empresa = $_GET['nombre_empresa'] ?? '';
$inicio = $_GET['inicio'] ?? ''; // Nueva fecha inicio
$fin = $_GET['fin'] ?? '';       // Nueva fecha fin

$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');

// Consulta base filtrada por la empresa
$query = "SELECT t.*, u.nombre as nombre_empleado
          FROM trabajos t 
          LEFT JOIN usuarios u ON t.usuario_id = u.id
          WHERE t.nombre_cliente = '" . mysqli_real_escape_string($conexion, $nombre_empresa) . "'";

// AÑADIMOS FILTRO DE FECHAS SI VIENEN EN LA URL
if (!empty($inicio) && !empty($fin)) {
    $inicioClean = mysqli_real_escape_string($conexion, $inicio);
    $finClean = mysqli_real_escape_string($conexion, $fin);
    $query .= " AND t.fecha BETWEEN '$inicioClean 00:00:00' AND '$finClean 23:59:59'";
}

$query .= " ORDER BY t.fecha DESC";

$resultado = mysqli_query($conexion, $query);
$sumaTotal = 0;
$filasHTML = "";

if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $sumaTotal += (float)$fila['precio_total'];
        $datosJSON = htmlspecialchars(json_encode($fila, JSON_HEX_QUOT | JSON_HEX_APOS), ENT_QUOTES, 'UTF-8');
        
        $filasHTML .= "<tr>
            <td class='col-fecha' style='font-size: 0.9rem;'>" . date("d/m/Y", strtotime($fila['fecha'])) . "</td>
            <td class='col-info'>
                <div style='font-weight:700; color: #2c3e50;'>📍 " . htmlspecialchars($fila['cliente']) . "</div>
                <div style='font-size:0.8rem; color:#666; font-style: italic;'>" . htmlspecialchars($fila['descripcion']) . "</div>
            </td>";
        
        if ($esAdmin) {
            $filasHTML .= "<td class='col-operario'><span style='background:#f0f0f0; padding:2px 6px; border-radius:4px; font-size:0.8rem;'>👤 " . strtoupper($fila['nombre_empleado']) . "</span></td>";
        }

        $filasHTML .= "
            <td class='col-total' style='font-weight:800; color:#2ecc71; text-align:right;'>
                " . number_format($fila['precio_total'], 2, ',', '.') . "€
            </td>
            <td class='col-accion' style='text-align:center;'>
                <button type='button' style='background:#3498db; color:white; border:none; padding:5px; border-radius:4px; cursor:pointer;' onclick='abrirEditarTrabajo($datosJSON)'>✏️</button>
            </td>
        </tr>";
    }
} else {
    $filasHTML = "<tr><td colspan='5' style='text-align:center; padding:40px; color:#95a5a6;'>No hay trabajos registrados en este periodo.</td></tr>";
}

echo $filasHTML;

// SCRIPT PARA ACTUALIZAR EL TOTAL FACTURADO EN LA INTERFAZ
echo "<script>
    if(document.getElementById('totalFacturadoEmpresa')) { 
        document.getElementById('totalFacturadoEmpresa').innerText = '" . number_format($sumaTotal, 2, ',', '.') . " €'; 
    }
</script>";
?>