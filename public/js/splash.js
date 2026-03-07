// Splash Screen Universal
(function() {
    'use strict';
    
    // Función para mostrar y ocultar el splash screen
    function initSplashScreen() {
        const splashScreen = document.getElementById('splashScreen');
        
        if (!splashScreen) return;
        
        // Ocultar splash screen después de 2 segundos
        setTimeout(() => {
            splashScreen.classList.add('fade-out');
            
            // Remover del DOM después de la animación
            setTimeout(() => {
                splashScreen.style.display = 'none';
            }, 500);
        }, 2000);
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSplashScreen);
    } else {
        initSplashScreen();
    }
    
    // También ejecutar cuando la página esté completamente cargada
    window.addEventListener('load', () => {
        // Asegurar que el splash se oculte incluso si hay recursos pesados
        const splashScreen = document.getElementById('splashScreen');
        if (splashScreen && !splashScreen.classList.contains('fade-out')) {
            setTimeout(() => {
                splashScreen.classList.add('fade-out');
                setTimeout(() => {
                    splashScreen.style.display = 'none';
                }, 500);
            }, 500);
        }
    });
})();
