<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'conexion.php';

// Detectamos si venimos de la vista de una empresa específica
$nombre_empresa = $_GET['nombre_empresa'] ?? '';
$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');

// Consulta base filtrada por la empresa
$query = "SELECT t.*, u.nombre as nombre_empleado
          FROM trabajos t 
          LEFT JOIN usuarios u ON t.usuario_id = u.id
          WHERE t.nombre_cliente = '" . mysqli_real_escape_string($conexion, $nombre_empresa) . "'";

$query .= " ORDER BY t.fecha DESC";

$resultado = mysqli_query($conexion, $query);
$sumaTotal = 0;
$filasHTML = "";

if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $sumaTotal += (float)$fila['precio_total'];
        $datosJSON = htmlspecialchars(json_encode($fila, JSON_HEX_QUOT | JSON_HEX_APOS), ENT_QUOTES, 'UTF-8');
        
        $filasHTML .= "<tr>
            <td class='col-fecha'>" . date("d/m/Y", strtotime($fila['fecha'])) . "</td>
            <td class='col-info'>
                <div style='font-weight:700;'>📍 " . htmlspecialchars($fila['cliente']) . "</div>
                <div style='font-size:0.8rem; color:#666;'>" . htmlspecialchars($fila['descripcion']) . "</div>
            </td>";
        
        if ($esAdmin) {
            $filasHTML .= "<td class='col-operario'>👤 " . strtoupper($fila['nombre_empleado']) . "</td>";
        }

        $filasHTML .= "
            <td class='col-total' data-precio='{$fila['precio_total']}' style='font-weight:800; color:#2ecc71;'>
                " . number_format($fila['precio_total'], 2, ',', '.') . "€
            </td>
            <td class='col-accion'>
                <button type='button' class='btn-accion' style='background:#3498db;' onclick='abrirEditarTrabajo($datosJSON)'>✏️</button>
            </td>
        </tr>";
    }
} else {
    $filasHTML = "<tr><td colspan='5' style='text-align:center; padding:20px;'>No hay trabajos registrados para esta empresa.</td></tr>";
}

echo $filasHTML;

// Script para actualizar el total de la empresa en la cabecera
echo "<script>
    if(document.getElementById('totalEmpresa')) { 
        document.getElementById('totalEmpresa').innerText = '" . number_format($sumaTotal, 2, ',', '.') . "€'; 
    }
</script>";
?>