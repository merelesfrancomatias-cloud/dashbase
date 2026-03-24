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
    const googleLoginBtn = document.getElementById('googleLoginBtn');

    const BASE = (function() {
        const parts = window.location.pathname.split('/').filter(Boolean);
        if (parts.length >= 1 && !parts[0].includes('.')) return '/' + parts[0];
        return '';
    })();

    let isGoogleLoading = false;
    let googleTokenClient = null;

    function hasGoogleGIS() {
        return !!(window.google && window.google.accounts && window.google.accounts.id);
    }

    function loadGoogleScript() {
        return new Promise((resolve) => {
            if (hasGoogleGIS()) {
                resolve(true);
                return;
            }

            const existing = document.querySelector('script[data-google-gsi="1"]');
            if (existing) {
                existing.addEventListener('load', () => resolve(hasGoogleGIS()), { once: true });
                existing.addEventListener('error', () => resolve(false), { once: true });
                setTimeout(() => resolve(hasGoogleGIS()), 2500);
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://accounts.google.com/gsi/client';
            script.async = true;
            script.defer = true;
            script.setAttribute('data-google-gsi', '1');
            script.onload = () => resolve(hasGoogleGIS());
            script.onerror = () => resolve(false);
            document.head.appendChild(script);

            setTimeout(() => resolve(hasGoogleGIS()), 2500);
        });
    }

    async function loginWithGoogleToken(accessToken) {
        if (!accessToken) {
            showAlert('No se pudo obtener token de Google', 'error');
            return;
        }

        if (isGoogleLoading) return;
        isGoogleLoading = true;
        googleLoginBtn.disabled = true;
        googleLoginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validando Google...';
        hideAlert();

        try {
            const loginRes = await fetch(`${BASE}/api/auth/login-google.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ access_token: accessToken }),
                credentials: 'include'
            });
            const loginData = await loginRes.json();

            if (loginData.success) {
                showAlert('Inicio con Google exitoso. Redirigiendo...', 'success');
                localStorage.setItem('user', JSON.stringify(loginData.data));
                setTimeout(() => {
                    window.location.href = 'views/dashboard/index.php';
                }, 900);
                return;
            }

            showAlert(loginData.message || 'No se pudo iniciar con Google', 'error');
        } catch (error) {
            console.error('Error Google login:', error);
            showAlert('Error de conexión al iniciar con Google', 'error');
        } finally {
            isGoogleLoading = false;
            googleLoginBtn.disabled = false;
            googleLoginBtn.innerHTML = '<i class="fab fa-google"></i> Ingresar con Google';
        }
    }

    async function initGoogleLogin() {
        if (!googleLoginBtn) return;

        try {
            const response = await fetch(`${BASE}/api/auth/google-config.php`, {
                method: 'GET',
                credentials: 'include'
            });
            const config = await response.json();

            if (!config.success || !config.data?.enabled || !config.data?.client_id) {
                return;
            }

            const gisReady = await loadGoogleScript();
            if (!gisReady || !window.google?.accounts?.oauth2) {
                googleLoginBtn.style.display = 'flex';
                googleLoginBtn.title = 'No se pudo cargar Google. Revisá AdBlock/antitracker y recargá.';
                return;
            }

            googleTokenClient = window.google.accounts.oauth2.initTokenClient({
                client_id: config.data.client_id,
                scope: 'openid email profile',
                callback: async (tokenResponse) => {
                    if (tokenResponse?.error) {
                        showAlert('Google canceló o bloqueó la autorización', 'error');
                        return;
                    }

                    await loginWithGoogleToken(tokenResponse?.access_token || '');
                }
            });

            googleLoginBtn.style.display = 'flex';
            googleLoginBtn.addEventListener('click', () => {
                if (!googleTokenClient) {
                    showAlert('Google no está disponible ahora', 'error');
                    return;
                }
                googleTokenClient.requestAccessToken({ prompt: 'select_account' });
            });
        } catch (error) {
            console.error('Google config error:', error);
        }
    }

    initGoogleLogin();

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
