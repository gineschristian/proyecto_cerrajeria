<?php
include 'conexion.php';

$inicio = $_GET['inicio'] ?? '';
$fin = $_GET['fin'] ?? '';

// Esta consulta combina los Trabajos y los Extras en una sola lista
$query = "(SELECT id, fecha, cliente AS concepto, precio_total AS monto, factura, 'trabajo' AS tipo FROM trabajos)
          UNION ALL
          (SELECT id, fecha, concepto, monto, 0 AS factura, 'extra' AS tipo FROM ingresos_b)";

// Filtro de fechas (aplicado a la combinación)
if (!empty($inicio) && !empty($fin)) {
    $query = "SELECT * FROM ($query) AS combinado WHERE fecha BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59' ORDER BY fecha DESC";
} else {
    $query = "SELECT * FROM ($query) AS combinado ORDER BY fecha DESC";
}

$res = mysqli_query($conexion, $query);

$sumaA = 0; // Oficial
$sumaB_de_trabajos = 0; // No facturado de trabajos
$sumaExtrasB = 0; // Lo de la tabla ingresos_b
$html = "";

while($row = mysqli_fetch_assoc($res)) {
    // Lógica para etiquetas y sumas
    if($row['tipo'] == 'trabajo') {
        if($row['factura'] == 1) {
            $sumaA += $row['monto'];
            $etiqueta = "<span style='color:green'>Oficial</span>";
        } else {
            $sumaB_de_trabajos += $row['monto'];
            $etiqueta = "<span style='color:orange'>Trabajo B</span>";
        }
    } else {
        $sumaExtrasB += $row['monto'];
        $etiqueta = "<span style='color:#e67e22'>Extra Manual</span>";
    }

    $datosParaModal = [
        'id' => $row['id'],
        'concepto' => $row['concepto'],
        'monto' => $row['monto']
    ];
    $jsonModal = htmlspecialchars(json_encode($datosParaModal), ENT_QUOTES, 'UTF-8');

    // AÑADIDOS LOS DATA-LABEL PARA EL RESPONSIVE
    $html .= "<tr>
                <td data-label='Fecha'>".date('d/m/Y', strtotime($row['fecha']))."</td>
                <td data-label='Concepto'>".$row['concepto']."</td>
                <td data-label='Tipo'>".$etiqueta."</td>
                <td data-label='Monto'><strong>".number_format($row['monto'], 2)."€</strong></td>
                <td data-label='Acciones'>
                    <button class='btn-header' style='background: #27ae60; padding: 5px 10px;' onclick='abrirEditarIngreso($jsonModal)'>✏️</button>
                </td>
              </tr>";
}

echo $html;

// Actualizamos los cuadros de arriba
$totalCajaTotal = $sumaA + $sumaB_de_trabajos; 

echo "<script>
    document.getElementById('totalOficial').innerText = '".number_format($sumaA, 2)."€';
    document.getElementById('totalB').innerText = '".number_format($sumaExtrasB, 2)."€';
    document.getElementById('totalGeneral').innerText = '".number_format($totalCajaTotal, 2)."€';
</script>";
?>