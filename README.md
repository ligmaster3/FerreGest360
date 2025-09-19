# FerreGest360 - Sistema de Gestión para Ferreterías

Sistema completo de gestión de inventario, ventas y administración para ferreterías desarrollado en PHP y MySQL.

## Características Principales

### 📦 Módulo de Inventario

- Gestión completa de productos con categorías y marcas
- Control de stock con alertas de mínimo
- Movimientos de inventario con trazabilidad
- Consultas optimizadas con paginación (máximo 20 elementos)
- Filtros avanzados por categoría, estado de stock y búsqueda
- Estadísticas en tiempo real

### 💰 Módulo de Ventas

- Gestión de facturas con estados (pendiente, pagada, vencida, anulada)
- Control de clientes (naturales y jurídicos)
- Sistema de pagos con múltiples formas de pago
- Consultas optimizadas con paginación (máximo 20 elementos)
- Filtros por cliente, estado, fechas y búsqueda
- Estadísticas de ventas y reportes

### 👥 Gestión de Usuarios

- Sistema de roles (admin, vendedor, bodeguero, contador)
- Control de acceso por módulos
- Seguridad con hash de contraseñas

## Instalación

### 1. Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)

## Estructura del Proyecto

```
FerreGest360/
├── config/
│   └── connection.php          # Configuración de base de datos
├── db/
│   ├── database.sql            # Estructura completa de la base de datos
│   ├── datos_ejemplo_ventas.sql # Datos de ejemplo para ventas
│   └── indices_optimizacion.sql # Índices para optimización
├── public/
│   ├── css/
│   │   └── styles.css          # Estilos del sistema
│   ├── js/
│   │   └── main.js             # JavaScript principal
│   ├── partials/
│   │   ├── head.php            # Head común
│   │   ├── header.php          # Header del sistema
│   │   ├── sidebar.php         # Menú lateral
│   │   ├── modals.php          # Modales del sistema
│   │   └── login.php           # Formulario de login
│   ├── inventario.php          # Módulo de inventario
│   ├── ventas.php              # Módulo de ventas
│   ├── productos.php           # Gestión de productos
│   ├── clientes.php            # Gestión de clientes
│   ├── proveedores.php         # Gestión de proveedores
│   ├── consultas_inventario.php # Consultas optimizadas de inventario
│   ├── consultas_ventas.php    # Consultas optimizadas de ventas
│   ├── optimizacion_consultas.php # Técnicas de optimización
│   └── index.php               # Página principal
└── README.md                   # Este archivo
```

## Optimizaciones Implementadas

### 🔧 Paginación Inteligente

- Máximo 20 elementos por página en todos los módulos
- Navegación eficiente con botones anterior/siguiente
- Indicadores de página actual

### 🔍 Filtros Avanzados

- Búsqueda por texto en múltiples campos
- Filtros por categoría, estado, fechas
- Filtros combinables para consultas precisas

### 📊 Consultas Optimizadas

- Uso de índices en campos críticos
- Consultas preparadas para seguridad
- Límites de seguridad para evitar sobrecarga
- Manejo de errores y casos edge

### 🎨 Interfaz de Usuario

- Diseño responsive y moderno
- Indicadores visuales de estado
- Botones de acción contextuales
- Mensajes informativos cuando no hay datos

## Datos de Ejemplo Incluidos

### Clientes de Prueba

- Juan Pérez (Natural) - Límite: $1,000
- Constructora ABC (Jurídico) - Límite: $5,000
- María González (Natural) - Límite: $500
- Ferretería Central (Jurídico) - Límite: $3,000
- Carlos Rodríguez (Natural) - Límite: $750

### Facturas de Ejemplo

- 10 facturas con diferentes estados
- Mix de pagos al contado y crédito
- Diferentes montos y descuentos
- Fechas de vencimiento variadas

### Productos de Ejemplo

- Martillo de garra 16oz - $25.00
- Destornillador plano 6" - $6.00
- Tornillo 1/4" x 2" - $0.10
- Pintura blanca 1 galón - $45.00
- Taladro eléctrico 1/2" - $125.00

## Próximos Pasos

### Funcionalidades Pendientes

- [ ] Modales para ver/editar facturas
- [ ] Sistema de exportación (PDF, Excel)
- [ ] Gráficos y reportes visuales
- [ ] Notificaciones de vencimiento
- [ ] Sistema de backup automático

### Mejoras Técnicas

- [ ] Cache de consultas frecuentes
- [ ] Logs de auditoría
- [ ] API REST para integraciones
- [ ] Sistema de permisos granular

## Soporte

Para soporte técnico o consultas sobre el sistema, contactar al equipo de desarrollo.

## Licencia

Este proyecto está bajo licencia MIT. Ver archivo LICENSE para más detalles.

---

**FerreGest360** - Solución completa para la gestión de ferreterías 🛠️
