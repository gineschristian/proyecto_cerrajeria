<?php
include 'conexion.php';

$query = "SELECT * FROM gastos ORDER BY fecha DESC";
$res = mysqli_query($conexion, $query);
$total = 0;
$html = "";

if (mysqli_num_rows($res) > 0) {
    while($row = mysqli_fetch_assoc($res)) {
        $total += $row['monto'];
        
        // Icono y etiqueta para la factura
        $iconFactura = ($row['con_factura'] == 1) ? "✅ Sí" : "❌ No";
        
        // Preparamos los datos para el modal de edición (Incluye automáticamente el campo 'proveedor')
        $datosGasto = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');

        $html .= "<tr>
                    <td data-label='Fecha'>".date('d/m/Y', strtotime($row['fecha']))."</td>
                    <td data-label='Proveedor' style='font-weight: bold; color: #2c3e50;'>".htmlspecialchars($row['proveedor'] ?? 'Otros')."</td>
                    <td data-label='Concepto'>".htmlspecialchars($row['concepto'])."</td>
                    <td data-label='Categoría'><span class='badge-cat'>".htmlspecialchars($row['categoria'])."</span></td>
                    <td data-label='Factura' style='text-align:center;'>{$iconFactura}</td>
                    <td data-label='Monto' style='color: #c0392b; font-weight: bold;'>-" . number_format($row['monto'], 2) . "€</td>
                    <td data-label='Acciones' style='text-align: center;'>
                        <div style='display: flex; gap: 15px; justify-content: center;'>
                            <button onclick='abrirEditarGasto($datosGasto)' title='Editar' style='background:none; border:none; cursor:pointer; font-size:1.2rem;'>✏️</button>
                            <button onclick='eliminarGasto({$row['id']})' title='Eliminar' style='background:none; border:none; cursor:pointer; font-size:1.2rem;'>🗑️</button>
                        </div>
                    </td>
                  </tr>";
    }
} else {
    // Aumentamos el colspan a 7 porque ahora hay una columna más
    $html = "<tr><td colspan='7' style='text-align:center; padding:20px;'>No hay gastos registrados.</td></tr>";
}

echo $html;

// Actualización automática del total en la interfaz
echo "<script>
    if(document.getElementById('totalGastos')) {
        document.getElementById('totalGastos').innerText = '" . number_format($total, 2) . "€';
    }
</script>";

$conexion->close();
?>