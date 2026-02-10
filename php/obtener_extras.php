<?php
include 'conexion.php';
$resultado = mysqli_query($conexion, "SELECT * FROM ingresos_b ORDER BY fecha DESC");
$total = 0;
$html = "";

while ($fila = mysqli_fetch_assoc($resultado)) {
    $total += $fila['monto'];
    $datosJSON = htmlspecialchars(json_encode($fila), ENT_QUOTES, 'UTF-8');
    
   // Dentro de tu bucle while en obtener_extras.php
$html .= "<tr>
        <td>" . date("d/m/Y", strtotime($fila['fecha'])) . "</td>
        <td>{$fila['concepto']}</td>
        <td style='font-weight:bold;'> " . number_format($fila['monto'], 2) . "€</td>
        <td>" . ($fila['origen'] ?? 'Manual') . "</td>
        <td>
            <div style='display: flex; gap: 5px;'>
                <button class='btn-header' style='background: #e67e22; padding: 5px 10px;' onclick='abrirEditarExtra($datosJSON)'>✏️</button>
                
                <button class='btn-header' style='background: #bc0000; padding: 5px 10px;' onclick='eliminarExtra({$fila['id']})'>🗑️</button>
            </div>
        </td>
    </tr>";
}

// Imprimimos las filas
echo $html;

// Enviamos el nuevo total en un input oculto para que JS lo lea
echo "<input type='hidden' id='nuevoTotalB' value='" . number_format($total, 2, '.', '') . "'>";
?>