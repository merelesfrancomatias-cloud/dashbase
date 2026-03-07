# 🖨️ Configuración de Impresora Térmica 80mm

## Características del Sistema de Impresión

✅ **Ticket optimizado para papel térmico de 80mm**
✅ **Formato automático al imprimir**
✅ **Diseño limpio y profesional**
✅ **Compatible con impresoras térmicas estándar**

## Impresoras Compatibles

El sistema funciona con cualquier impresora térmica de 80mm que sea compatible con Windows, macOS o Linux:

- **Epson TM-T20II / TM-T88V**
- **Star TSP143III**
- **Bixolon SRP-350**
- **Citizen CT-S310II**
- **POS-80**
- **Cualquier impresora térmica con driver ESC/POS**

## Configuración Paso a Paso

### 1. Instalar Driver de la Impresora

1. Descarga el driver de tu impresora desde el sitio del fabricante
2. Instala el driver siguiendo las instrucciones
3. Conecta la impresora (USB, Ethernet o Bluetooth)
4. Configura la impresora como predeterminada (opcional)

### 2. Configurar Tamaño de Papel

#### Windows:
1. Ve a **Panel de Control > Dispositivos e Impresoras**
2. Clic derecho en tu impresora > **Preferencias de impresión**
3. En **Tamaño de papel**, selecciona o crea:
   - **Ancho**: 80mm (o 3.15 inches)
   - **Alto**: Continuo o 297mm
4. Guarda la configuración

#### macOS:
1. Ve a **Preferencias del Sistema > Impresoras y Escáneres**
2. Selecciona tu impresora
3. En **Tamaño de papel**, crea un tamaño personalizado:
   - **Ancho**: 80mm
   - **Alto**: 200mm (o automático)

### 3. Configurar en el Navegador

#### Para Chrome/Edge:
1. Al imprimir, ve a **Más opciones**
2. Desactiva **Encabezados y pies de página**
3. Establece márgenes en **Ninguno** o **Mínimo**
4. Marca **Gráficos en segundo plano** (si está disponible)

#### Para Firefox:
1. Archivo > **Configurar página**
2. Desactiva encabezados y pies
3. Establece márgenes en 0

## Prueba de Impresión

Para probar tu impresora:

1. Realiza una venta de prueba
2. Al completar la venta, aparecerá un modal con el botón **"Imprimir Ticket"**
3. Haz clic en **"Imprimir Ticket"**
4. Se abrirá una ventana de vista previa
5. Verifica que el formato se vea correcto
6. Haz clic en **Imprimir**

## Personalización del Ticket

Puedes personalizar el ticket editando el archivo:
```
/public/js/ticket-config.js
```

### Opciones disponibles:

```javascript
{
    nombre_negocio: 'Tu Negocio',
    slogan: 'Tu slogan',
    telefono: '1234-5678',
    direccion: 'Tu dirección',
    mensaje_agradecimiento: 'Gracias por su compra',
    mensaje_despedida: 'Vuelva pronto'
}
```

## Contenido del Ticket

El ticket incluye:

- ✅ Logo/Nombre del negocio
- ✅ Número de ticket
- ✅ Fecha y hora
- ✅ Nombre del cajero
- ✅ Método de pago
- ✅ Lista de productos con cantidades y precios
- ✅ Subtotal
- ✅ Descuentos (si aplica)
- ✅ Total
- ✅ Mensaje de agradecimiento

## Solución de Problemas

### El ticket no se imprime:

1. **Verifica que la impresora esté encendida y conectada**
2. **Comprueba que sea la impresora predeterminada**
3. **Asegúrate de tener papel en la impresora**
4. **Revisa que los drivers estén actualizados**

### El formato se ve mal:

1. **Verifica el tamaño de papel configurado (debe ser 80mm)**
2. **Desactiva márgenes en la configuración de impresión**
3. **Usa Chrome o Edge para mejor compatibilidad**

### Los caracteres se ven cortados:

1. **Ajusta el tamaño de fuente en ticket-config.js**
2. **Reduce el ancho de las líneas si es necesario**
3. **Verifica que la codificación sea UTF-8**

## Impresión en Red

Para compartir la impresora en red:

1. Comparte la impresora desde el equipo donde está conectada
2. En otros equipos, agrega la impresora de red
3. Configura el mismo tamaño de papel en todos los equipos

## Notas Importantes

⚠️ **Siempre haz una prueba antes de usar en producción**
⚠️ **Mantén el driver de la impresora actualizado**
⚠️ **Usa papel térmico de calidad para mejores resultados**
⚠️ **El ticket se imprime automáticamente después de confirmar la venta**

## Soporte

Para más ayuda con la configuración de impresoras:

- Consulta el manual de tu impresora
- Contacta al fabricante de la impresora
- Revisa la documentación de drivers ESC/POS

---

**Desarrollado por DASH4 CRM** 🚀
