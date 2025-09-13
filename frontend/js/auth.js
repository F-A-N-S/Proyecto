// frontend/assets/js/auth.js

// --- API_BASE_URL (AHORA GLOBAL) ---
// Se ha movido aquí para que sea accesible desde cualquier script que la necesite (ej. backoffice.js)
// Asegúrate de que esta URL sea correcta para la ubicación de tu api.php
// frontend/js/auth.js
const API_BASE_URL = '/fans-cooperativa_2da_entrega_responsive_v3/frontend/api/api.php';


document.addEventListener('DOMContentLoaded', function() {
    // ... todo el resto del código de auth.js ...
});
// Si por alguna razón pusiste api.php directamente en fans-cooperativa/frontend/api.php, usa:
// const API_BASE_URL = './api.php'; 
// --- FIN API_BASE_URL GLOBAL ---


document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const registerForm = document.getElementById('registerForm');
    const loginForm = document.getElementById('loginForm');
    const logoutBtn = document.getElementById('logoutBtn');

    // showNotification AHORA VIENE DE main.js (porque se ha movido allí para ser global)
    // No la definas aquí, solo asegúrate de que main.js se carga antes que auth.js en tus HTML.


    // Funciones de validación (Mantenidas como están)
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function showFieldError(input, message) {
        // Implementación visual de error en campo específico (si la necesitas)
        // Por ahora, solo usamos showNotification para mensajes globales.
    }

    function clearFieldError(input) {
        // Implementación para limpiar error visual de campo específico
    }

    // Manejo del formulario de Contacto (Mantenido como está)
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            showNotification('¡Mensaje enviado con éxito! Te contactaremos pronto.', 'success');
            contactForm.reset();
        });
    }

    // Manejo del formulario de Registro (Mantenido como está, ya funciona)
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Recoger datos del formulario (los IDs deben coincidir con los de tu HTML)
            const nombre = document.getElementById('nombre').value.trim();
            const apellido = document.getElementById('apellido').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value; 
            const fecha_ingreso = document.getElementById('fecha_ingreso').value; // Formato YYYY-MM-DD
            const cedula = document.getElementById('cedula').value.trim() || null; // Opcional
            const telefono = document.getElementById('telefono').value.trim() || null; // Opcional

            // Validaciones Front-end antes de enviar
            let isValid = true;
            if (password !== confirmPassword) { showNotification('Las contraseñas no coinciden.', 'error'); isValid = false; }
            if (password.length < 8) { showNotification('La contraseña debe tener al menos 8 caracteres.', 'error'); isValid = false; }
            if (!validateEmail(correo)) { showNotification('Ingrese un correo electrónico válido.', 'error'); isValid = false; }
            if (!nombre || !apellido || !correo || !password || !fecha_ingreso) { showNotification('Por favor, complete todos los campos obligatorios (*).', 'error'); isValid = false; }
            if (!isValid) return;

            const userData = { nombre, apellido, cedula, correo, telefono, fecha_ingreso, password };

            try {
                // LLAMADA A LA API CON LA NUEVA RUTA Y PARÁMETRO ACTION
                const response = await fetch(`${API_BASE_URL}?action=register`, {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(userData)
                });
                const result = await response.json();
                if (response.ok) {
                    showNotification(result.message || 'Registro exitoso. Su cuenta está pendiente de aprobación.', 'success');
                    registerForm.reset(); setTimeout(() => { window.location.href = 'login.html'; }, 2000);
                } else { showNotification(result.message || 'Error en el registro. El correo podría ya estar en uso o hubo un problema en el servidor.', 'error'); }
            } catch (error) { console.error('Error de red al registrar:', error); showNotification('Error de conexión con el servidor. Inténtelo de nuevo más tarde.', 'error'); }
        });
    }

    // Manejo del formulario de Login (Mantenido como está, ya funciona)
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const correo = document.getElementById('loginEmail').value.trim(); 
            const password = document.getElementById('loginPassword').value; 

            if (!correo || !password) { showNotification('Por favor, ingrese su correo y contraseña.', 'error'); return; }

            const loginData = { correo, password };

            try {
                // LLAMADA A LA API CON LA NUEVA RUTA Y PARÁMETRO ACTION
                const response = await fetch(`${API_BASE_URL}?action=login`, {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(loginData)
                });
                const result = await response.json();
                if (response.ok) {
                   showNotification(result.message || 'Inicio de sesión exitoso.', 'success');
                   // PATCH: normalizar id para mantener compatibilidad
const u = result.user || {};
if (u.id && !u.id_Residente) u.id_Residente = u.id;
localStorage.setItem('fans_user_data', JSON.stringify(u));


localStorage.setItem('fans_user_data', JSON.stringify(result.user));
setTimeout(() => {
  const u = result.user || {};
  const target = (u.role === 'admin') ? 'backoffice.html' : 'dashboard.html';
  window.location.href = target;
}, 1000);



                } else { showNotification(result.message || 'Credenciales inválidas o cuenta no aprobada.', 'error'); }
            } catch (error) { console.error('Error de red al iniciar sesión:', error); showNotification('Error de conexión con el servidor. Inténtelo de nuevo más tarde.', 'error'); }
        });
    }

    // Cargar datos de usuario en Dashboard (AJUSTADO: Condición de ejecución y población)
    if (window.location.pathname.includes('dashboard.html')) { 
        const userData = JSON.parse(localStorage.getItem('fans_user_data'));
        if (userData) {
            // Actualizar elementos en el header
            const userNameHeader = document.getElementById('userNameHeader');
            if (userNameHeader) userNameHeader.textContent = userData.Nombre;

            // Actualizar elementos en el dashboard
            const dashboardUserName = document.getElementById('dashboardUserName');
            if (dashboardUserName) dashboardUserName.textContent = userData.Nombre;
            
            // Poblar los datos personales con control de existencia de elementos
            const fullName = document.getElementById('fullName');
            if (fullName) fullName.textContent = userData.Nombre + ' ' + userData.Apellido; 
            
            const userEmail = document.getElementById('userEmail');
            if (userEmail) userEmail.textContent = userData.Correo; 
            
            const userCedula = document.getElementById('userCedula');
            if (userCedula) userCedula.textContent = userData.Cedula || 'No especificada'; 
            
            const userTelefono = document.getElementById('userTelefono');
            if (userTelefono) userTelefono.textContent = userData.Telefono || 'No especificado'; 
            
            const userFechaIngreso = document.getElementById('userFechaIngreso');
            if (userFechaIngreso) { 
                 const date = new Date(userData.Fecha_Ingreso);
                 userFechaIngreso.textContent = date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
            }

            const accountStatusElement = document.getElementById('accountStatus');
            if (accountStatusElement) {
                accountStatusElement.textContent = userData.estado_aprobacion ? 'Aprobada' : 'Pendiente de Aprobación';
                accountStatusElement.className = userData.estado_aprobacion ? 'info-value status-approved' : 'info-value status-pending'; 
            }

        } else { // Si no hay datos de usuario en localStorage o no estamos en dashboard.html, redirigir al login
            window.location.href = 'login.html'; 
        }
    }

    // Manejo del botón de Logout (Mantenido como está, ya funciona)
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            localStorage.removeItem('fans_user_data');
            showNotification('Sesión cerrada exitosamente.', 'success');
            setTimeout(() => { window.location.href = 'index.html'; }, 1000);
        });
    }
});