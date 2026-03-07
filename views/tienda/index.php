<!DOCTYPE html>
<?php
session_start();
$negocioId = 0;
if (!empty($_SESSION['negocio_id']))  $negocioId = (int)$_SESSION['negocio_id'];
elseif (!empty($_GET['negocio_id']))  $negocioId = (int)$_GET['negocio_id'];
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta — DASH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/tienda.css">
</head>
<body>

<!-- ── Header ──────────────────────────────────────────────── -->
<header class="tienda-header">
    <div class="container">
        <a href="#" class="logo">
            <div class="logo-icon-wrap">
                <img src="../../public/img/DASHLOGOSF.png" alt="DASH" id="logoImg">
            </div>
            <span class="logo-nombre" id="logoNombre">Carta</span>
        </a>
        <div class="header-search">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar productos...">
            </div>
        </div>
        <div class="header-actions">
            <button class="btn-icon" id="themeToggle" title="Cambiar tema">
                <i class="fas fa-moon"></i>
            </button>
            <button class="carrito-btn" onclick="toggleCarrito()">
                <i class="fas fa-shopping-bag"></i>
                <span>Carrito</span>
                <span class="badge" id="carritoCount">0</span>
            </button>
        </div>
    </div>
</header>

<!-- Buscador sticky en móvil -->
<div class="search-mobile-fixed">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInputMobile" placeholder="Buscar productos...">
    </div>
</div>

<!-- ── Hero ────────────────────────────────────────────────── -->
<section class="hero-banner">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge">
                    <i class="fas fa-store"></i> Carta Digital
                </div>
                <h1 class="hero-title" id="heroTitle">Bienvenido</h1>
                <p class="hero-subtitle" id="heroSubtitle">
                    Explorá nuestros productos y hacé tu pedido fácilmente por WhatsApp.
                </p>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-number" id="totalProductos">—</span>
                    <span class="hero-stat-label">Productos</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-number" id="totalCategorias">—</span>
                    <span class="hero-stat-label">Categorías</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Categorías ──────────────────────────────────────────── -->
<section class="categorias-section">
    <div class="container">
        <div class="section-label">Categorías</div>
        <div class="carrusel-wrapper">
            <button class="carrusel-btn prev" onclick="tienda.scrollCategorias(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="categorias-carrusel" id="categoriasCarrusel">
                <div class="categoria-card active" data-categoria="">
                    <div class="categoria-icon"><i class="fas fa-th-large"></i></div>
                    Todos
                </div>
                <!-- categorías dinámicas -->
            </div>
            <button class="carrusel-btn next" onclick="tienda.scrollCategorias(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

<!-- ── Productos ───────────────────────────────────────────── -->
<section class="productos-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Productos</h2>
            <span class="section-count" id="productoCount">Cargando...</span>
        </div>
        <div id="productosGrid" class="productos-grid"></div>
        <div id="productosSkeleton" class="productos-grid" style="display:none"></div>
        <div id="noResultados" class="no-resultados" style="display:none">
            <i class="fas fa-box-open"></i>
            <p>No se encontraron productos</p>
        </div>
    </div>
</section>

<!-- ── Panel Carrito ───────────────────────────────────────── -->
<div id="carritoPanel" class="carrito-panel">
    <div class="carrito-header">
        <h2><i class="fas fa-shopping-bag"></i> Mi Pedido</h2>
        <button class="close-btn" onclick="toggleCarrito()"><i class="fas fa-times"></i></button>
    </div>
    <div id="carritoItems" class="carrito-items"></div>
    <div class="carrito-footer">
        <div class="total-container">
            <span>Total</span>
            <span class="total-precio" id="totalPrecio">$0</span>
        </div>
        <button class="btn-whatsapp" onclick="enviarPedidoWhatsApp()">
            <i class="fab fa-whatsapp"></i> Enviar pedido por WhatsApp
        </button>
        <button class="btn-limpiar" onclick="limpiarCarrito()">
            <i class="fas fa-trash"></i> Vaciar carrito
        </button>
    </div>
</div>
<div id="carritoOverlay" class="carrito-overlay" onclick="toggleCarrito()"></div>

<!-- WhatsApp flotante -->
<a href="#" id="whatsappFloat" class="whatsapp-float" title="Contactar por WhatsApp" style="display:none">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- Loading -->
<div id="loadingSpinner" class="loading-spinner">
    <i class="fas fa-spinner fa-spin"></i>
</div>

<!-- Footer -->
<footer class="tienda-footer">
    <div class="container">
        <p>Powered by <span>DASH</span> — Sistema de Gestión Inteligente</p>
    </div>
</footer>

<script>
const TIENDA_NEGOCIO_ID = <?= $negocioId ?>;

// Dark mode
const savedTheme = localStorage.getItem('tienda_theme') || 'light';
if (savedTheme === 'dark') document.body.classList.add('dark-mode');
document.getElementById('themeToggle').addEventListener('click', () => {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('tienda_theme', isDark ? 'dark' : 'light');
    document.querySelector('#themeToggle i').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
});
if (savedTheme === 'dark') document.querySelector('#themeToggle i').className = 'fas fa-sun';
</script>
<script src="../../public/js/tienda.js?v=<?= filemtime(__DIR__ . '/../../public/js/tienda.js') ?>"></script>
</body>
</html>
