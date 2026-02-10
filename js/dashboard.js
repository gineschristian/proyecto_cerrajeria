 document.addEventListener('DOMContentLoaded', () => {
            fetch('../php/consultar_alertas.php')
                .then(res => res.json())
                .then(data => {
                    const contenedor = document.getElementById('contenedorAlertas');
                    if (data.length > 0) {
                        data.forEach(item => {
                            const alertDiv = document.createElement('div');
                            const esCritico = item.cantidad == 0;
                            
                            alertDiv.className = 'alerta-stock';
                            alertDiv.style.backgroundColor = esCritico ? '#ff4d4d' : '#ffa502';
                            alertDiv.style.color = 'white';
                            
                            alertDiv.innerHTML = `
                                <span>${esCritico ? '🚫' : '⚠️'}</span>
                                <span><strong>${esCritico ? 'SIN STOCK' : 'STOCK BAJO'}:</strong> ${item.nombre} - Unidades: ${item.cantidad}</span>
                            `;
                            contenedor.appendChild(alertDiv);
                        });
                    }
                })
                .catch(err => console.error("Error cargando alertas:", err));
        });
