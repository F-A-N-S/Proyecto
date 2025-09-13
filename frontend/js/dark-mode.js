// frontend/assets/js/dark-mode.js

document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    const themeIcon = document.querySelector('.theme-icon');

    function applyTheme(theme) {
        if (theme === 'dark-mode') {
            body.classList.add('dark-mode');
            body.classList.remove('light-mode');
            themeIcon.textContent = '☀️'; // Icono de sol para modo claro
            themeToggle.setAttribute('aria-label', 'Cambiar a modo claro');
        } else {
            body.classList.remove('dark-mode');
            body.classList.add('light-mode');
            themeIcon.textContent = '🌙'; // Icono de luna para modo oscuro
            themeToggle.setAttribute('aria-label', 'Cambiar a modo oscuro');
        }
    }

    // Cargar la preferencia del usuario o del sistema
    const savedTheme = localStorage.getItem('fans-theme'); // Usamos una clave más específica y segura
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    // Aplicar el tema al cargar la página
    if (savedTheme) {
        applyTheme(savedTheme);
    } else {
        // Si NO hay preferencia guardada, siempre iniciar en light-mode.
        // Esto ignora la preferencia del sistema operativo al inicio si no hay una elección manual previa.
        applyTheme('light-mode'); 
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            if (body.classList.contains('dark-mode')) {
                applyTheme('light-mode');
                localStorage.setItem('fans-theme', 'light-mode');
            } else {
                applyTheme('dark-mode');
                localStorage.setItem('fans-theme', 'dark-mode');
            }
        });
    }

    // Escuchar cambios en la preferencia del sistema (si el usuario cambia el tema del SO)
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
        // Solo aplicar si el usuario no ha establecido una preferencia manual
        if (!localStorage.getItem('fans-theme')) { 
            applyTheme(event.matches ? 'dark-mode' : 'light-mode');
        }
    });
});