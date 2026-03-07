<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - DASH CRM</title>
    <link rel="manifest" href="/DASHBASE/manifest.json">
    <meta name="theme-color" content="#0FD186">
    <link rel="stylesheet" href="public/css/splash.css">
    <link rel="stylesheet" href="public/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Splash Screen -->
    <div id="splashScreen" class="splash-screen">
        <div class="splash-content">
            <img src="public/img/Splash.png" alt="DASH" class="splash-logo">
            <div class="splash-loader"></div>
        </div>
    </div>

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="login-logo">
                    <div class="logo-gradient">
                        <img src="public/img/logo.png" alt="DASH Logo" class="logo-img">
                    </div>
                </div>
                <p class="login-subtitle">Gestiona tu negocio de forma inteligente</p>
            </div>

            <!-- Contenedor de alertas -->
            <div id="alertContainer" class="alert hidden"></div>

            <form id="loginForm" autocomplete="off">
                <div class="form-group">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input 
                        type="text" 
                        id="usuario" 
                        class="form-input" 
                        placeholder="Ingresa tu usuario"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input 
                        type="password" 
                        id="password" 
                        class="form-input" 
                        placeholder="Ingresa tu contraseña"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span id="btnText">Iniciar Sesión</span>
                    <div id="btnSpinner" class="spinner hidden"></div>
                </button>
            </form>

            <div class="login-divider">
                <span>¿No tenés cuenta?</span>
            </div>

            <a href="register.php" class="btn btn-register">
                <i class="fas fa-store"></i> Registrar mi negocio
            </a>
        </div>
    </div>

    <script src="public/js/splash.js"></script>
    <script src="public/js/login.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .catch(() => {}); // silencioso si falla (ej: localhost sin HTTPS)
            });
        }
    </script>
</body>
</html>
 