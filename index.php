<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Cerrajería Pinos</title>
    
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="img/logo_pwa_192.png">

    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="img/logo_pwa_192.png">
    <meta name="theme-color" content="#2c3e50">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('sw.js');
        });
      }
    </script>
    
    <link rel="stylesheet" href="css/login.css">
</head>
    <style>
        /* Tu estilo CSS se mantiene igual... */
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #000000 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-card img {
            width: 120px;
            margin-bottom: 20px;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: #219150;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <img src="img/logo.png" alt="Logo Cerrajería Pinos">
        
        <h2>Gestión de Acceso</h2>

        <form action="php/auth.php" method="POST">
            <div class="input-group" style="text-align: left; margin-bottom: 20px;">
                <label for="usuario" style="display: block; color: #666; font-size: 0.9em;">Nombre de Usuario</label>
                <input type="text" name="usuario" id="usuario" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;" required placeholder="Introduce tu usuario">
            </div>

            <div class="input-group" style="text-align: left; margin-bottom: 20px;">
                <label for="password" style="display: block; color: #666; font-size: 0.9em;">Contraseña</label>
                <input type="password" name="password" id="password" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn-login">Entrar al Sistema</button>
        </form>

        <div id="mensajeError" class="error-msg"></div>
    </div>

</body>
</html>