// Splash Screen
window.addEventListener('load', () => {
    const splashScreen = document.getElementById('splashScreen');
    
    // Ocultar splash screen después de 2 segundos
    setTimeout(() => {
        splashScreen.classList.add('fade-out');
        setTimeout(() => {
            splashScreen.style.display = 'none';
        }, 500);
    }, 2000);
});

// Manejo del formulario de login
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const alertContainer = document.getElementById('alertContainer');

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const usuario = document.getElementById('usuario').value.trim();
        const password = document.getElementById('password').value;
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        // Validaciones
        if (!usuario || !password) {
            showAlert('Por favor completa todos los campos', 'error');
            return;
        }

        // Deshabilitar botón y mostrar loading
        submitBtn.disabled = true;
        btnText.textContent = 'Iniciando sesión...';
        btnSpinner.classList.remove('hidden');
        hideAlert();

        try {
            const response = await fetch('api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ usuario, password })
            });

            const data = await response.json();

            if (data.success) {
                showAlert('Inicio de sesión exitoso. Redirigiendo...', 'success');
                
                // Guardar datos en localStorage
                localStorage.setItem('user', JSON.stringify(data.data));
                
                // Redirigir al dashboard
                setTimeout(() => {
                    window.location.href = 'views/dashboard/index.php';
                }, 1000);
            } else {
                showAlert(data.message || 'Error al iniciar sesión', 'error');
                submitBtn.disabled = false;
                btnText.textContent = 'Iniciar Sesión';
                btnSpinner.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error de conexión. Por favor intenta nuevamente.', 'error');
            submitBtn.disabled = false;
            btnText.textContent = 'Iniciar Sesión';
            btnSpinner.classList.add('hidden');
        }
    });

    // Función para mostrar alertas
    function showAlert(message, type = 'error') {
        alertContainer.className = `alert alert-${type} show`;
        alertContainer.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
            <span>${message}</span>
        `;
    }

    // Función para ocultar alertas
    function hideAlert() {
        alertContainer.classList.remove('show');
        alertContainer.classList.add('hidden');
    }

    // Verificar si ya hay una sesión activa
    checkSession();
});

// Verificar sesión activa
async function checkSession() {
    // Si venimos de un logout explícito, no redirigir (y limpiar el flag)
    if (sessionStorage.getItem('just_logged_out') === '1') {
        sessionStorage.removeItem('just_logged_out');
        return;
    }
    // Compatibilidad con ?logout=1 (links viejos)
    if (new URLSearchParams(window.location.search).get('logout') === '1') return;

    try {
        const response = await fetch('api/auth/check.php', {
            method: 'GET',
            credentials: 'include'
        });

        const data = await response.json();

        if (data.success) {
            // Ya hay sesión activa, redirigir al dashboard
            window.location.href = 'views/dashboard/index.php';
        }
    } catch (error) {
        // No hay sesión activa, continuar en login
        console.log('No hay sesión activa');
    }
}
