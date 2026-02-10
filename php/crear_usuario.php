<?php
include 'conexion.php';

// Verificamos que los datos no estén vacíos
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$user = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$pass_plana = isset($_POST['password']) ? trim($_POST['password']) : '';
$rol = isset($_POST['rol']) ? $_POST['rol'] : 'empleado';

if (!empty($nombre) && !empty($user) && !empty($pass_plana)) {
    // Encriptamos la contraseña
    $pass_hash = password_hash($pass_plana, PASSWORD_DEFAULT);

    // Preparamos la consulta
    $sql = "INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssss", $nombre, $user, $pass_hash, $rol);
        
        if ($stmt->execute()) {
            echo "✅ Empleado registrado correctamente.";
        } else {
            echo "❌ Error al insertar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "❌ Error en la preparación de la consulta: " . $conexion->error;
    }
} else {
    echo "❌ Por favor, rellena todos los campos del formulario.";
}
?>