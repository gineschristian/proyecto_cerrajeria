<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificación de seguridad: solo el admin puede crear categorías
if (!isset($_SESSION['usuario_id']) || strtolower(trim($_SESSION['rol'])) !== 'admin') {
    echo "❌ Acceso denegado.";
    exit();
}

include 'conexion.php';

// 2. Validamos que se haya recibido el nombre
if (isset($_POST['nombre']) && !empty(trim($_POST['nombre']))) {
    
    // Limpiamos el texto para evitar inyecciones SQL y caracteres extraños
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    
    // 3. Comprobamos si la categoría ya existe (para evitar errores de duplicado)
    $check = mysqli_query($conexion, "SELECT id FROM categorias_stock WHERE nombre = '$nombre'");
    
    if (mysqli_num_rows($check) > 0) {
        echo "⚠️ Esta categoría ya existe.";
    } else {
        // 4. Insertamos la nueva categoría
        $sql = "INSERT INTO categorias_stock (nombre) VALUES ('$nombre')";
        
        if (mysqli_query($conexion, $sql)) {
            echo "✅ Categoría guardada con éxito.";
        } else {
            echo "❌ Error al guardar en la base de datos: " . mysqli_error($conexion);
        }
    }
} else {
    echo "⚠️ Por favor, escribe un nombre válido.";
}

// Cerramos la conexión
mysqli_close($conexion);
?>