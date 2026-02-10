<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/login.html");
    exit();
}
if ($_SESSION['rol'] !== 'admin') {
    header("Location: dashboard.php?error=acceso_denegado");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2c3e50">
    <title>Impuestos - Cerrajería Pinos</title>
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
            padding: 30px; 
            border-radius: 12px; 
            color: white; 
            text-align: center; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .iva-pagar { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .iva-soportado { background: linear-gradient(135deg, #3498db, #2980b9); }
        .beneficio-neto { background: linear-gradient(135deg, #27ae60, #219150); }
        
        .card-impuesto h3 { margin-bottom: 10px; font-size: 1.1em; opacity: 0.9; }
        .card-impuesto h2 { font-size: 2.2em; margin: 10px 0; }

        /* Ajuste para filtros en móvil */
        @media (max-width: 768px) {
            .filtros-fecha {
                flex-direction: column;
                align-items: stretch !important;
                gap: 10px;
            }
            .filtros-fecha input, .btn-filtro {
                width: 100%;
            }
            .header-tabla-dinamica {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            <h1>Impuestos y Balance</h1>
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
            <div class="header-tabla-dinamica" style="display:flex; justify-content: space-between; align-items: center; gap: 15px;">
                <h2 style="margin:0; border:none;">Cálculo de Periodo</h2>
                <div class="filtros-fecha" style="display:flex; align-items:center; gap:10px;">
                    <input type="date" id="fechaInicioImp" class="input-style-mini">
                    <input type="date" id="fechaFinImp" class="input-style-mini">
                    <button onclick="calcularImpuestos()" class="btn-filtro" style="background: #2c3e50; border:none; color:white; padding: 10px 20px; border-radius:5px; cursor:pointer;">Calcular</button>
                </div>
            </div>
        </section>

        <div class="grid-impuestos" id="contenedorBalance">
            <?php include '../php/procesar_impuestos.php'; ?>
        </div>

        <section class="table-card" style="width: 100%; margin-top: 20px; padding: 20px;">
            <h2 style="text-align: center; margin-bottom: 20px;">Comparativa Mensual</h2>
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

        function calcularImpuestos() {
            const inicio = document.getElementById('fechaInicioImp').value;
            const fin = document.getElementById('fechaFinImp').value;

            if(!inicio || !fin) {
                alert("Por favor, selecciona ambas fechas.");
                return;
            }

            fetch(`../php/procesar_impuestos.php?inicio=${inicio}&fin=${fin}`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('contenedorBalance').innerHTML = html;
                })
                .catch(err => console.error("Error al calcular:", err));
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
                            plugins: {
                                legend: { position: 'bottom' }
                            },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                });
        }
    </script>
</body>
</html>