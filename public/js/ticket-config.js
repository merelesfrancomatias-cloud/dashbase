// Configuración de Ticket de Impresión 80mm
// Este archivo permite personalizar la apariencia del ticket

const TICKET_CONFIG = {
    // Información del negocio
    nombre_negocio: 'DASH4 CRM',
    slogan: 'Sistema de Punto de Venta',
    
    // Información de contacto (opcional)
    telefono: '',
    email: '',
    direccion: '',
    website: '',
    
    // Configuración de impresión
    ancho_papel: '80mm', // 80mm para impresoras térmicas estándar
    fuente: 'Courier New, monospace',
    tamano_fuente: '11px',
    
    // Mensajes personalizados
    mensaje_agradecimiento: '¡Gracias por su compra!',
    mensaje_despedida: 'Vuelva pronto',
    
    // Mostrar/ocultar elementos
    mostrar_logo: false,
    mostrar_codigo_qr: false,
    mostrar_iva: false,
    
    // Estilo de separadores
    separador: '═══════════════════════════',
    separador_items: '- - - - - - - - - - - - - -',
    
    // Formato de números
    simbolo_moneda: '$',
    decimales: 2
};

// Exportar configuración
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TICKET_CONFIG;
}
