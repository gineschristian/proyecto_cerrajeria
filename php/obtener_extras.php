<?php
include 'conexion.php';

// 1. Capturamos las fechas si existen
$inicio = isset($_GET['inicio']) ? $_GET['inicio'] : '';
$fin = isset($_GET['fin']) ? $_GET['fin'] : '';

// 2. Construimos la consulta base
$query = "SELECT * FROM ingresos_b";

// 3. Si hay fechas, filtramos por el rango
if (!empty($inicio) && !empty($fin)) {
    $query .= " WHERE fecha BETWEEN '$inicio' AND '$fin'";
}

$query .= " ORDER BY fecha DESC";

$resultado = mysqli_query($conexion, $query);
$total = 0;
$html = "";

if (mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $total += $fila['monto'];
        $datosJSON = htmlspecialchars(json_encode($fila), ENT_QUOTES, 'UTF-8');
        
        $html .= "<tr>
                <td>" . date("d/m/Y", strtotime($fila['fecha'])) . "</td>
                <td>{$fila['concepto']}</td>
                <td style='font-weight:bold;'> " . number_format($fila['monto'], 2, ',', '.') . "€</td>
                <td>" . ($fila['origen'] ?? 'Manual') . "</td>
                <td>
                    <div style='display: flex; gap: 5px;'>
                        <button class='btn-header' style='background: #e67e22; padding: 5px 10px;' onclick='abrirEditarExtra($datosJSON)'>✏️</button>
                        <button class='btn-header' style='background: #bc0000; padding: 5px 10px;' onclick='eliminarExtra({$fila['id']})'>🗑️</button>
                    </div>
                </td>
            </tr>";
    }
} else {
    $html = "<tr><td colspan='5' style='text-align:center;'>No hay registros en este periodo.</td></tr>";
}

// Imprimimos las filas
echo $html;

// Enviamos el nuevo total en un input oculto para que JS lo pueda captar si fuera necesario
echo "<input type='hidden' id='nuevoTotalB' value='" . number_format($total, 2, '.', '') . "'>";
?>