<?php
// Ya que este archivo se incluye en stock.php, ya tenemos acceso a $_SESSION
include 'conexion.php';

// Verificamos el rol (si no está definido, por seguridad tratamos como empleado)
$esAdmin = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin');

// Ordenamos por categoría primero y luego por cantidad
$query = "SELECT * FROM productos ORDER BY categoria ASC, cantidad ASC";
$resultado = mysqli_query($conexion, $query);

if (mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $cantidad = $fila['cantidad'];
        
        $datosJSON = htmlspecialchars(json_encode($fila), ENT_QUOTES, 'UTF-8');

        // Lógica de estados para las clases CSS
        if ($cantidad >= 10) { 
            $clase = "ok"; $texto = "Suficiente"; 
        } elseif ($cantidad > 0) { 
            $clase = "low"; $texto = "Bajo"; 
        } else { 
            $clase = "empty"; $texto = "Agotado"; 
        }

        $ruta_img = "../img/productos/" . $fila['imagen'];
        
        echo "<tr>";
        // Añadimos data-label a cada celda para el diseño responsive
        echo "<td data-label='Imagen'><img src='$ruta_img' class='img-tabla' onerror=\"this.src='../img/productos/default.jpg'\"></td>";
        echo "<td data-label='Nombre'>" . htmlspecialchars($fila['nombre']) . "</td>";
        echo "<td data-label='Categoría'>" . htmlspecialchars($fila['categoria']) . "</td>";
        echo "<td data-label='Cantidad'>" . $cantidad . "</td>";
        echo "<td data-label='Estado'><span class='status-badge $clase'>$texto</span></td>";
        
        // --- BLOQUE DE ACCIONES PROTEGIDO ---
        echo "<td data-label='Acciones' style='text-align:center;'>";
        if ($esAdmin) {
            echo "<button class='btn-header' style='background:#2980b9; margin-right:5px;' onclick='abrirEditarStock($datosJSON)'>✏️</button>";
            echo "<button class='btn-header' style='background:#c0392b;' onclick='eliminar(" . $fila['id'] . ")'>🗑️</button>";
        } else {
            echo "<span style='color:#95a5a6; font-size:0.9em;'>🔒 Solo lectura</span>";
        }
        echo "</td>";
        // -------------------------------------
        
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>No hay productos registrados.</td></tr>";
}
$conexion->close();
?>