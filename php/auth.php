<?php
ob_start(); 
session_start();
include 'conexion.php';

$user = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($user) && !empty($pass)) {
        $sql = "SELECT * FROM usuarios WHERE usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($fila = $resultado->fetch_assoc()) {
            if (password_verify($pass, $fila['password'])) {
                $_SESSION['usuario_id'] = $fila['id'];
                $_SESSION['nombre'] = $fila['nombre'];
                
                // --- LÍNEA CLAVE AÑADIDA ---
                // Guardamos el rol de la base de datos en la sesión
                $_SESSION['rol'] = $fila['rol']; 
                
                // RUTA CORREGIDA: 
                // Antes tenías ../admin/dashboard.php, pero tu archivo está en la raíz del proyecto
                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                echo "Contraseña incorrecta.";
            }
        } else {
            echo "Usuario no encontrado.";
        }
    }
} else {
    header("Location: ../../index.html");
    exit();
}
ob_end_flush();
?>