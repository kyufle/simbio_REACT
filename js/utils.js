const contenedorToast = document.getElementById('contenedor-toast');

const cerrarToast = function(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.add('cerrando');
    }
};

/* Agrega un nuevo toast al contenedor */
const agregarToast = function(tipo, titulo, descripcion) {
    const nuevoToast = document.createElement('div');
    nuevoToast.classList.add('toast', tipo);

    const toastId = 'toast-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
    nuevoToast.id = toastId;

    const iconos = {
        exito: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>`,
        error: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-exclamation-octagon-fill" viewBox="0 0 16 16">
                <path d="M11.46.146A.5.5 0 0 0 11.107 0H4.893a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353zM8 4c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
            </svg>`,
        info: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
            </svg>`,
        warning: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
            </svg>`
    };

    // Construir HTML del toast
    nuevoToast.innerHTML = `
        <div class="contenido">
            <div class="icono">${iconos[tipo]}</div>
            <div class="texto">
                <p class="titulo">${titulo}</p>
                <p class="descripcion">${descripcion}</p>
            </div>
        </div>
        <button class="btn-cerrar" aria-label="Cerrar notificación">
            <div class="icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                </svg>
            </div>
        </button>
    `;

    // Agregar al contenedor
    contenedorToast.appendChild(nuevoToast);

    // Scroll suave hacia el nuevo toast
    setTimeout(() => {
        if (contenedorToast.scrollHeight > contenedorToast.clientHeight) {
            contenedorToast.scrollTo({
                top: contenedorToast.scrollHeight,
                behavior: 'smooth'
            });
        }
    }, 100);

    // Listener para eliminar el toast cuando termine la animación
    nuevoToast.addEventListener('animationend', function(e) {
        if (e.animationName === 'slideOut') {
            nuevoToast.remove();
        }
    });
};

// Event listener para cerrar toasts
if (contenedorToast) {
    contenedorToast.addEventListener('click', function(e) {
        const toastElement = e.target.closest('div.toast');
        if (toastElement && e.target.closest('button.btn-cerrar')) {
            cerrarToast(toastElement.id);
        }
    });
}

// FUNCIONES GLOBALES - Usar en toda la app

/* Muestra un toast de éxito */
window.mostrarExito = function(titulo, descripcion) {
    agregarToast('exito', titulo, descripcion);
};

/* Muestra un toast de error */
window.mostrarError = function(titulo, descripcion) {
    agregarToast('error', titulo, descripcion);
};

/* Muestra un toast de información */
window.mostrarInfo = function(titulo, descripcion) {
    agregarToast('info', titulo, descripcion);
};

/* Muestra un toast de advertencia */
window.mostrarAdvertencia = function(titulo, descripcion) {
    agregarToast('warning', titulo, descripcion);
};

// MANEJADOR GLOBAL DE ERRORES 

// Capturar errores de JavaScript no controlados
window.addEventListener('error', function(event) {
    agregarToast('error', 'Error', event.message || 'Ha ocurrido un error inesperado');
    console.error('Error capturado:', event);
});

// Capturar promesas rechazadas no manejadas
window.addEventListener('unhandledrejection', function(event) {
    agregarToast('error', 'Error', (event.reason && event.reason.message) || 'Error en operación asíncrona');
    console.error('Promise rechazada:', event.reason);
});