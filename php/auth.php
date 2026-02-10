<?php
session_start();
include 'conexion.php';

$user = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

if (!empty($user) && !empty($pass)) {
    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($fila = $resultado->fetch_assoc()) {
        // DEPUREMOS: Descomenta la línea de abajo si sigue fallando para ver qué hay en la BD
        // die("BD Hash: " . $fila['password'] . " | Pass escrita: " . $pass);

        if (password_verify($pass, $fila['password'])) {
            $_SESSION['usuario_id'] = $fila['id'];
            $_SESSION['nombre'] = $fila['nombre'];
            $_SESSION['rol'] = $fila['rol'];
            
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            echo "❌ La contraseña no coincide con el hash de la base de datos.";
        }
    } else {
        echo "❌ El usuario '$user' no existe en la base de datos.";
    }
}
?>