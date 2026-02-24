<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    // Si intenta entrar sin login, lo mandamos a la raíz donde está el index.html
    header("Location: ../index.html");
    exit();
}
$esAdmin = ($_SESSION['rol'] === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2c3e50">
    <title>Panel de Control - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* AJUSTES RESPONSIVE ESPECÍFICOS PARA EL DASHBOARD */
        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s;
            height: 100%;
        }

        /* Clase para que la gráfica ocupe dos columnas en pantallas grandes */
        .card-wide {
            grid-column: span 2;
        }

        .card:active {
            transform: scale(0.98); /* Efecto de pulsación en móvil */
        }
        /* Ajuste para que las tarjetas de gráficas tengan espacio suficiente */
        .card-grafica-ajustada {
        min-height: 400px !important; /* Le damos un poco más de aire */
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        }

        @media (max-width: 900px) {
            .card-wide {
                grid-column: span 1;
            }
        }

        @media (max-width: 600px) {
            header {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            .header-content h1 {
                font-size: 1.2rem;
                margin-top: 10px;
            }
            .dashboard-container {
                grid-template-columns: 1fr; /* Una sola columna en móviles pequeños */
                padding: 10px;
            }
        }

        /* Estilos alertas */
        .alerta-stock {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <header style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background: #2c3e50; color: white;">
        <div class="header-content" style="display: flex; align-items: center; gap: 15px;">
            <img src="../img/logo.png" alt="Logo" class="logo-img" style="height: 50px;">
            <h1 style="margin: 0;">Hola David, <?php echo explode(' ', htmlspecialchars($_SESSION['nombre']))[0]; ?></h1>
        </div>
        <a href="../php/logout.php"><button style="background:#e74c3c; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">Cerrar Sesión</button></a>
    </header>

    <main class="dashboard-container">
        
        <div id="contenedorAlertas" style="grid-column: 1 / -1;"></div>

        <div class="card">
            <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">🛠️</div>
            <h3>Trabajos</h3>
            <p>Registro de clientes y partes diarios.</p>
            <a href="trabajos.php" class="btn-acceder">Acceder</a>
        </div>

        <div class="card">
            <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">📦</div>
            <h3>Stock</h3>
            <p>Inventario de material y llaves.</p>
            <a href="stock.php" class="btn-acceder">Acceder</a>
        </div>

        <div class="card">
            <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">🗒️</div>
            <h3>Plantillas</h3>
            <p>Documentos y guías del taller.</p>
            <a href="plantillas.php" class="btn-acceder">Acceder</a>
        </div>

        <?php if ($esAdmin): ?>

            <div class="card" style="border-top: 5px solid #2ecc71;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">💰</div>
                <h3>Ingresos</h3>
                <p>Control de facturación oficial (A).</p>
                <a href="ingresos.php" class="btn-acceder">Gestionar</a>
            </div>

            <div class="card" style="border-top: 5px solid #e74c3c;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">💸</div>
                <h3>Gastos</h3>
                <p>Compras y pagos a proveedores.</p>
                <a href="gastos.php" class="btn-acceder">Gestionar</a>
            </div>

            <div class="card" style="border-top: 5px solid #f1c40f;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">🤫</div>
                <h3>Extras</h3>
                <p>Caja secundaria y otros registros.</p>
                <a href="ingresosb.php" class="btn-acceder">Gestionar</a>
            </div>

            <div class="card card-wide" style="border-top: 5px solid #3498db; background: white; padding: 20px; border-radius: 12px;">
                <h3 style="margin-bottom: 15px; color: #2c3e50;">📊 Facturación por Localidad (Mes Actual) </h3>
                <div style="height: 250px; width: 100%;">
                    <canvas id="graficaLocalidades"></canvas>
                </div>
            </div>
            <div class="card card-grafica-ajustada" style="border-top: 5px solid #2ecc71;">
            <h3 style="color: #2c3e50; margin-bottom: 10px; font-size: 1.1rem; text-align: center;">
            📈 Balance Ingresos vs Gastos
            </h3>
            <div style="position: relative; height: 200px; width: 100%;"> <canvas id="graficaBalance"></canvas>
            </div>
            <div id="infoBeneficio" style="text-align: center; margin-top: 10px;"></div>
            </div>
            </div>

            <div class="card" style="border-top: 5px solid #e67e22;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">📊</div>
                <h3>Impuestos</h3>
                <p>Cierres trimestrales e IVA.</p>
                <a href="impuestos.php" class="btn-acceder">Gestionar</a>
            </div>

            <div class="card" style="border-top: 5px solid #8e44ad;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">👥</div>
                <h3>Empleados</h3>
                <p>Alta y baja de usuarios.</p>
                <a href="gestion_usuarios.php" class="btn-acceder">Gestionar</a>
            </div>

            <div class="card" style="border-top: 5px solid #34495e;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">🛡️</div>
                <h3>Seguridad</h3>
                <p>Copia de seguridad del sistema.</p>
                <a href="../php/backup.php" class="btn-acceder" style="background: #34495e; color: white;">
                    📥 Backup .SQL
                </a>
            </div>

             <div class="card" style="border-top: 5px solid #73b4f5;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">🏢</div>
                <h3>Empresas</h3>
                <p>Empresas de trabajo</p>
                <a href="empresas.php" class="btn-acceder" style="background: #34495e; color: white;">
                    Gestionar
                </a>
            </div>

        <?php endif; ?>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/dashboard.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('../php/datos_grafica_localidad.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('graficaLocalidades').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { 
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) { return value + '€'; }
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            })
            .catch(err => console.error("Error cargando gráfica:", err));
    });
    // Gráfica de Balance (Ingresos vs Gastos)
// Gráfica de Balance (Ingresos vs Gastos)
fetch('../php/datos_grafica_balance.php')
    .then(response => response.json())
    .then(data => {
        const ctxBalance = document.getElementById('graficaBalance').getContext('2d');
        
        const ingresos = data.datasets[0].data[0] || 0;
        const gastos = data.datasets[0].data[1] || 0;
        const beneficio = ingresos - gastos;
        const colorBeneficio = beneficio >= 0 ? '#2ecc71' : '#e74c3c';

        new Chart(ctxBalance, {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10 } }
                }
            }
        });

        // Inyectamos el beneficio en el hueco reservado
        const divInfo = document.getElementById('infoBeneficio');
        divInfo.innerHTML = `
            <p style="margin: 0; font-size: 0.8rem; color: #7f8c8d; font-weight: bold;">BENEFICIO NETO (MES)</p>
            <p style="margin: 0; font-size: 1.4rem; color: ${colorBeneficio}; font-weight: bold;">
                ${beneficio.toLocaleString('es-ES', { minimumFractionDigits: 2 })}€
            </p>
        `;
    });
    </script>
    
</body>
</html>