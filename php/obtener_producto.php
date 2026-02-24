<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'conexion.php';

$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');

// 1. Obtener lista de usuarios para las columnas dinámicas
$usuarios_query = "SELECT id, nombre FROM usuarios ORDER BY id ASC";
$res_usuarios = mysqli_query($conexion, $usuarios_query);
$lista_usuarios = [];
while ($u = mysqli_fetch_assoc($res_usuarios)) {
    $lista_usuarios[] = $u;
}

// 2. Consulta principal con SUM dinámico de la nueva tabla stock_usuarios
$query = "SELECT p.*, 
          (p.cant_almacen + IFNULL((SELECT SUM(cantidad) FROM stock_usuarios WHERE id_producto = p.id), 0)) as total_global 
          FROM productos p 
          ORDER BY p.categoria ASC, p.nombre ASC";
$resultado = mysqli_query($conexion, $query);

if (mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $id_p = $fila['id'];
        $total = $fila['total_global'];
        
        // 3. Obtener el stock por cada usuario para este producto específico
        $stock_detalle = [];
        $q_stock = "SELECT id_usuario, cantidad FROM stock_usuarios WHERE id_producto = $id_p";
        $res_stock = mysqli_query($conexion, $q_stock);
        while($s = mysqli_fetch_assoc($res_stock)) {
            $stock_detalle[$s['id_usuario']] = (int)$s['cantidad'];
        }

        // Metemos el desglose en el JSON para que el Modal lo use
        $fila['desglose_stock'] = $stock_detalle;
        $datosJSON = htmlspecialchars(json_encode($fila), ENT_QUOTES, 'UTF-8');

        // Lógica de colores/estado
        if ($total >= 10) { $clase = "ok"; $texto = "Suficiente"; }
        elseif ($total > 0) { $clase = "low"; $texto = "Bajo"; }
        else { $clase = "empty"; $texto = "Agotado"; }

        $ruta_img = "../img/productos/" . $fila['imagen'];
        
        echo "<tr>";
        echo "<td data-label='Imagen'><img src='$ruta_img' class='img-tabla' onerror=\"this.src='../img/productos/default.jpg'\"></td>";
        echo "<td data-label='Nombre'>" . htmlspecialchars($fila['nombre']) . "</td>";
        echo "<td data-label='Categoría'>" . htmlspecialchars($fila['categoria']) . "</td>";
        
        // --- COLUMNAS DINÁMICAS ---
        // Taller (Almacén central fijo en la tabla productos)
        echo "<td data-label='Taller' style='font-weight:bold;'>".$fila['cant_almacen']."</td>";

        // Iteramos por los usuarios (Furgonetas)
        foreach ($lista_usuarios as $user) {
            $cant = isset($stock_detalle[$user['id']]) ? $stock_detalle[$user['id']] : 0;
            // IMPORTANTE: Añadimos data-user-id para que el JS sepa quién es quién
            echo "<td data-label='F. ".$user['nombre']."' data-user-id='".$user['id']."'>$cant</td>";
        }

        echo "<td data-label='Total' style='font-weight:bold; background: #f8f9fa;'>$total</td>";
        echo "<td data-label='Estado'><span class='status-badge $clase'>$texto</span></td>";
        
        echo "<td data-label='Acciones' style='text-align:center;'>";
        if ($esAdmin) {
            echo "<button class='btn-header' style='background:#2980b9; margin-right:5px;' onclick='abrirEditarStock($datosJSON)'>✏️</button>";
            echo "<button class='btn-header' style='background:#c0392b;' onclick='eliminar($id_p)'>🗑️</button>";
        } else {
            echo "<span style='color:#95a5a6; font-size:0.9em;'>🔒 Lectura</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='20' style='text-align:center; padding:20px;'>No hay productos registrados.</td></tr>";
}
$conexion->close();
?>