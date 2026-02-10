// Manejar el Registro de Usuarios
document.getElementById('formNuevoUsuario').addEventListener('submit', function(e) {
    e.preventDefault(); // Evita que la página se recargue o se desvíe

    const formData = new FormData(this);

    fetch('../php/crear_usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes("✅")) {
            alert("¡Empleado registrado con éxito!");
            location.reload(); // Recarga para mostrar el nuevo usuario en la tabla
        } else {
            alert("Error: " + data);
        }
    })
    .catch(error => console.error('Error:', error));
});

// Función para Eliminar (la que ya tenías)
function eliminarUsuario(id) {
    if (confirm("¿Estás seguro de que quieres eliminar a este empleado?")) {
        fetch('../php/eliminar_usuario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'success') {
                location.reload(); 
            } else {
                alert("Error al eliminar.");
            }
        });
    }
}