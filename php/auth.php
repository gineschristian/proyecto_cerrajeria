<?php
ob_start(); 
session_start();
include 'conexion.php'; // Verifica que este archivo tenga los datos de Sered

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
                $_SESSION['rol'] = $fila['rol']; 
                
                // REDIRECCIÓN TRAS LOGIN ÉXITOSO
                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                echo "Contraseña incorrecta. <a href='../index.php'>Volver</a>";
            }
        } else {
            echo "Usuario no encontrado. <a href='../index.php'>Volver</a>";
        }
    }
} else {
    // Si intentan entrar a auth.php sin POST, los mandamos al inicio del proyecto
    header("Location: ../index.php");
    exit();
}
ob_end_flush();
?>