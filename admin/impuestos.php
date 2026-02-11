<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos si existe la sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.html");
    exit();
}

// 2. Verificamos el rol
$rol = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : '';

if ($rol !== 'admin') {
    header("Location: dashboard.php?error=acceso_denegado");
    exit();
}

include '../php/conexion.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Impuestos y Estrategia - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
    <style>
        .grid-impuestos { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 20px; 
            margin-top: 20px; 
            width: 100%;
        }
        .card-impuesto { 
            padding: 25px; 
            border-radius: 12px; 
            color: white; 
            text-align: center; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card-impuesto:hover { transform: translateY(-5px); }
        
        .iva-pagar { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .iva-soportado { background: linear-gradient(135deg, #3498db, #2980b9); }
        .beneficio-neto { background: linear-gradient(135deg, #27ae60, #219150); }
        
        .card-impuesto h3 { margin-bottom: 10px; font-size: 1.1em; opacity: 0.9; }
        .card-impuesto h2 { font-size: 2em; margin: 10px 0; }
        
        /* Estilos Simulador */
        .simulador-box {
            background: #f8f9f9;
            border-left: 5px solid #3498db;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .nav-container { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 10px; }
        @media (max-width: 768px) {
            header { flex-direction: column; padding: 15px; }
            .filtros-fecha { flex-direction: column; width: 100%; }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            <h1>Estrategia Fiscal / Impuestos - Cerrajeria Pinos</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="gastos.php" class="btn-header">💸 Gastos</a>
            <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
            <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <main class="trabajos-layout" style="padding: 15px;">
        
        <section class="table-card" style="width: 100%; margin-bottom: 20px; padding: 15px;">
            <div class="header-tabla-dinamica" style="display:flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <h2 style="margin:0; border:none;">Periodo de Análisis</h2>
                <div class="filtros-fecha" style="display:flex; align-items:center; gap:10px;">
                    <input type="date" id="fechaInicioImp" class="input-style-mini" value="<?php echo date('Y-m-01'); ?>">
                    <input type="date" id="fechaFinImp" class="input-style-mini" value="<?php echo date('Y-m-t'); ?>">
                    <button onclick="calcularImpuestos()" class="btn-filtro" style="background: #2c3e50; color:white; border:none; padding: 10px; border-radius:5px; cursor:pointer;">Actualizar Datos</button>
                </div>
            </div>
        </section>

        <section class="simulador-box">
            <h3 style="margin-top:0; color: #2c3e50;">🧪 Simulador de Inversión en Material</h3>
            <p style="font-size: 0.9rem; color: #5d6d7e; margin-bottom: 15px;">Calcula cuánto ahorrarías en impuestos si compras material hoy (IVA incluido):</p>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input type="number" id="montoSimulacion" placeholder="Cantidad en €" class="input-style-mini" style="width: 180px; padding: 10px;">
                <button onclick="simularGasto()" class="btn-filtro" style="background: #3498db; color:white; border:none; padding: 10px 20px; border-radius:5px; cursor:pointer;">Simular Ahorro</button>
                <button onclick="calcularImpuestos()" class="btn-filtro" style="background: #95a5a6; color:white; border:none; padding: 10px 20px; border-radius:5px; cursor:pointer;">Resetear</button>
            </div>
        </section>

        <div class="grid-impuestos" id="contenedorBalance">
            <?php include '../php/procesar_impuestos.php'; ?>
        </div>

        <section class="table-card" style="width: 100%; margin-top: 20px; padding: 20px;">
            <h2 style="text-align: center; margin-bottom: 20px;">Tendencia Ingresos vs Gastos</h2>
            <div style="position: relative; height:300px; width:100%">
                <canvas id="graficaBalance"></canvas>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            renderizarGrafica();
        });

        // Función normal de cálculo
        function calcularImpuestos() {
            const inicio = document.getElementById('fechaInicioImp').value;
            const fin = document.getElementById('fechaFinImp').value;
            document.getElementById('montoSimulacion').value = ""; // Limpiar simulador

            fetch(`../php/procesar_impuestos.php?inicio=${inicio}&fin=${fin}`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('contenedorBalance').innerHTML = html;
                })
                .catch(err => console.error("Error:", err));
        }

        // Función del Simulador
        function simularGasto() {
            const inicio = document.getElementById('fechaInicioImp').value;
            const fin = document.getElementById('fechaFinImp').value;
            const gasto = document.getElementById('montoSimulacion').value;

            if(!gasto || gasto <= 0) {
                alert("Por favor, introduce un monto válido para simular.");
                return;
            }

            // Enviamos el parámetro 'simulacion' al PHP
            fetch(`../php/procesar_impuestos.php?inicio=${inicio}&fin=${fin}&simulacion=${gasto}`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('contenedorBalance').innerHTML = html;
                    // Scroll suave hacia los resultados para ver el impacto
                    document.getElementById('contenedorBalance').scrollIntoView({ behavior: 'smooth' });
                })
                .catch(err => console.error("Error en simulación:", err));
        }

        function renderizarGrafica() {
            fetch('../php/obtener_datos_grafica.php')
                .then(res => res.json())
                .then(datos => {
                    const ctx = document.getElementById('graficaBalance').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: datos.labels,
                            datasets: [
                                {
                                    label: 'Ingresos (€)',
                                    data: datos.ingresos,
                                    backgroundColor: '#27ae60',
                                    borderRadius: 5
                                },
                                {
                                    label: 'Gastos (€)',
                                    data: datos.gastos,
                                    backgroundColor: '#e74c3c',
                                    borderRadius: 5
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                });
        }
    </script>
</body>
</html>