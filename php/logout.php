<?php
session_start();
session_unset();
session_destroy();

// RUTA CORREGIDA: Sale de php/, sale de proyecto_cerrajeria/ y llega a index.html
header("Location: ../../index.html"); 
exit();
?>