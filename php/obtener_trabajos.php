<?php
include 'conexion.php';

// Leemos las fechas si existen
$inicio = $_GET['inicio'] ?? '';
$fin = $_GET['fin'] ?? '';

$query = "SELECT t.*, p.nombre AS nombre_producto 
          FROM trabajos t 
          LEFT JOIN productos p ON t.producto_usado = p.id ";

// Filtro de fechas (Protección básica contra inyección)
if (!empty($inicio) && !empty($fin)) {
    $inicioClean = mysqli_real_escape_string($conexion, $inicio);
    $finClean = mysqli_real_escape_string($conexion, $fin);
    $query .= " WHERE t.fecha BETWEEN '$inicioClean 00:00:00' AND '$finClean 23:59:59' ";
}

$query .= " ORDER BY t.fecha DESC";

$resultado = mysqli_query($conexion, $query);
$sumaTotal = 0;
$filasHTML = "";

if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $sumaTotal += $fila['precio_total'];
        
        // Datos para el Modal
        $datosJSON = htmlspecialchars(json_encode($fila), ENT_QUOTES, 'UTF-8');
        
        $iconoFactura = ($fila['factura'] == 1) ? "<span style='color: #27ae60; font-weight:bold;'>📄 SÍ</span>" : "<span style='color: #888;'>❌ NO</span>";
        
        $filasHTML .= "<tr>
            <td data-label='Fecha'>" . date("d/m/Y", strtotime($fila['fecha'])) . "</td>
            <td data-label='Cliente'><strong>" . htmlspecialchars($fila['cliente']) . "</strong></td>
            <td data-label='Descripción'>" . htmlspecialchars($fila['descripcion']) . "</td>
            <td data-label='Factura' style='text-align:center;'>$iconoFactura</td>
            <td data-label='Material'>" . ($fila['nombre_producto'] ?? '<small style="color:#bbb;">Solo mano obra</small>') . "</td>
            <td data-label='Total' style='color: #27ae60; font-weight: bold; font-size: 1.1rem;'>" . number_format($fila['precio_total'], 2) . "€</td>
            <td data-label='Acciones'>
                <div style='display: flex; gap: 10px; justify-content: center;'>
                    <button class='btn-header' style='background: #27ae60; padding: 10px; border:none; border-radius:5px; cursor:pointer;' onclick='abrirEditarTrabajo($datosJSON)'>✏️</button>
                    <button class='btn-header' style='background: #e74c3c; padding: 10px; border:none; border-radius:5px; cursor:pointer;' onclick='eliminarTrabajo({$fila['id']})'>🗑️</button>
                </div>
            </td>
        </tr>";
    }
} else {
    $filasHTML = "<tr><td colspan='7' style='text-align:center; padding: 30px; color: #7f8c8d;'>No hay trabajos registrados en este periodo.</td></tr>";
}

echo $filasHTML;

// Actualizamos el total en la interfaz
echo "<script>
    if(document.getElementById('totalFacturado')) {
        document.getElementById('totalFacturado').innerText = '" . number_format($sumaTotal, 2) . "€';
    }
</script>";

$conexion->close();
?>