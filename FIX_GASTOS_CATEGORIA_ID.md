# Fix: Error "Column not found: categoria_id" en Gastos

## 🐛 Error Original
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'categoria_id' in 'field list'
```

## 🔍 Causa del Problema

La **API de gastos estaba desactualizada** y no coincidía con la estructura real de la base de datos.

### Estructura Real de la Tabla `gastos`:
```sql
- id (INT)
- negocio_id (INT)
- usuario_id (INT)
- caja_id (INT)
- categoria (ENUM) ← No categoria_id
- descripcion (TEXT) ← No hay concepto separado
- monto (DECIMAL)
- metodo_pago (ENUM)
- comprobante (VARCHAR)
- fecha_gasto (TIMESTAMP) ← No fecha
```

### Valores ENUM de `categoria`:
- `compra_mercaderia`
- `servicios`
- `salarios`
- `alquiler`
- `impuestos`
- `otros`

### Valores ENUM de `metodo_pago`:
- `efectivo`
- `tarjeta_debito`
- `tarjeta_credito`
- `transferencia`
- `otro`

## ✅ Soluciones Aplicadas

### 1. API de Gastos (`api/gastos/index.php`)

#### GET - Obtener gastos
**Antes:**
```php
SELECT g.*, u.nombre as usuario_nombre, c.nombre as categoria_nombre
FROM gastos g
LEFT JOIN usuarios u ON g.usuario_id = u.id
LEFT JOIN categorias c ON g.categoria_id = c.id  -- ❌ No existe
WHERE g.fecha >= ?  -- ❌ Columna incorrecta
```

**Después:**
```php
SELECT g.*, u.nombre as usuario_nombre
FROM gastos g
LEFT JOIN usuarios u ON g.usuario_id = u.id
WHERE g.fecha_gasto >= ?  -- ✅ Columna correcta
```

#### POST - Crear gasto
**Cambios clave:**
- ✅ Verificación de caja abierta antes de crear gasto
- ✅ `categoria_id` → `categoria` (ENUM)
- ✅ `fecha` → `fecha_gasto`
- ✅ `concepto` se guarda en `descripcion`
- ✅ Si hay `descripcion` adicional, se concatena: "concepto - descripcion"
- ✅ Validación de valores ENUM para categoría

```php
// Concatenar concepto y descripción
$descripcion = $data['concepto'];
if (isset($data['descripcion']) && !empty($data['descripcion'])) {
    $descripcion .= ' - ' . $data['descripcion'];
}

// Validar categoría ENUM
$categoria = $data['categoria_id'] ?? 'otros';
if (!in_array($categoria, ['compra_mercaderia', 'servicios', 'salarios', 'alquiler', 'impuestos', 'otros'])) {
    $categoria = 'otros';
}
```

#### PUT - Actualizar gasto
**Cambios:**
- ✅ `fecha` → `fecha_gasto`
- ✅ `categoria_id` → `categoria` con validación ENUM
- ✅ Manejo correcto de concepto + descripción

### 2. Vista HTML (`views/gastos/index.php`)

**Antes:**
```html
<select class="form-control" id="categoriaId">
    <option value="">Sin categoría</option>
    <!-- Se llenaba dinámicamente desde la API de categorías -->
</select>
```

**Después:**
```html
<select class="form-control" id="categoriaId">
    <option value="otros">Otros</option>
    <option value="compra_mercaderia">Compra de Mercadería</option>
    <option value="servicios">Servicios</option>
    <option value="salarios">Salarios</option>
    <option value="alquiler">Alquiler</option>
    <option value="impuestos">Impuestos</option>
</select>
```

**Filtros agregados:**
- ✅ Select de categoría en filtros
- ✅ Actualizado select de métodos de pago (débito/crédito)

### 3. JavaScript (`public/js/gastos.js`)

#### Eliminado:
```javascript
async loadCategorias() {
    // Ya no es necesario cargar categorías desde la API
}
```

#### Actualizado - renderGastos():
```javascript
const categoriaLabels = {
    'compra_mercaderia': 'Compra Mercadería',
    'servicios': 'Servicios',
    'salarios': 'Salarios',
    'alquiler': 'Alquiler',
    'impuestos': 'Impuestos',
    'otros': 'Otros'
};

// Extraer concepto de la descripción
const concepto = gasto.descripcion.split(' - ')[0];
const descripcionExtra = gasto.descripcion.includes(' - ') ? 
    gasto.descripcion.substring(gasto.descripcion.indexOf(' - ') + 3) : '';

// Usar fecha_gasto
const fecha = new Date(gasto.fecha_gasto);
```

#### Actualizado - editGasto():
```javascript
// Separar concepto y descripción
const concepto = gasto.descripcion.split(' - ')[0];
const descripcionExtra = gasto.descripcion.includes(' - ') ? 
    gasto.descripcion.substring(gasto.descripcion.indexOf(' - ') + 3) : '';

document.getElementById('concepto').value = concepto;
document.getElementById('descripcion').value = descripcionExtra;
document.getElementById('fecha').value = gasto.fecha_gasto.split(' ')[0];
document.getElementById('categoriaId').value = gasto.categoria || 'otros';
```

#### Nuevo - Filtro de categoría:
```javascript
const categoria = document.getElementById('categoriaFiltro')?.value || '';
if (categoria) url += `categoria=${categoria}&`;
```

## 📋 Archivos Modificados

1. **`api/gastos/index.php`**
   - Queries GET, POST, PUT actualizados
   - Eliminado JOIN con tabla categorias
   - Validación de ENUM categoria
   - Verificación de caja abierta en POST

2. **`views/gastos/index.php`**
   - Select de categoría con valores ENUM
   - Filtro de categoría agregado
   - Métodos de pago actualizados

3. **`public/js/gastos.js`**
   - Eliminada función loadCategorias
   - renderGastos actualizado para fecha_gasto y categoria
   - editGasto con separación de concepto/descripción
   - Filtro de categoría agregado

## 🎯 Resultado Final

✅ Los gastos se pueden crear sin errores de base de datos  
✅ Los gastos se listan correctamente con fecha_gasto  
✅ Las categorías se muestran con labels bonitos  
✅ El filtro por categoría funciona  
✅ Edición de gastos maneja correctamente concepto y descripción  
✅ Se requiere caja abierta para registrar gastos  
✅ Los métodos de pago incluyen débito y crédito  

## 📊 Mapeo de Campos

| Frontend (Form) | Backend (API) | Base de Datos |
|-----------------|---------------|---------------|
| concepto | concepto | descripcion (parte 1) |
| descripcion | descripcion | descripcion (parte 2) |
| categoriaId | categoria_id | categoria (ENUM) |
| fecha | fecha | fecha_gasto (TIMESTAMP) |
| metodoPagoForm | metodo_pago | metodo_pago (ENUM) |

## 🔒 Validaciones Agregadas

1. **Caja abierta**: No se pueden registrar gastos sin caja abierta
2. **Categoría ENUM**: Si el valor no es válido, se usa "otros"
3. **Concatenación**: Concepto + descripción se guardan juntos
4. **Fecha**: Se convierte correctamente el formato para fecha_gasto

---

**Fecha del Fix:** 31 de Octubre, 2025  
**Archivos Modificados:** 3  
**Estado:** ✅ Completado y Probado
