# Plugin de Control de Horarios para WordPress

Este plugin proporciona un sistema de registro y control de horarios laborales para WordPress, permitiendo a los empleados registrar sus entradas y salidas diarias. Es una herramienta ideal para pequeñas y medianas empresas que necesitan llevar un control del tiempo trabajado por sus empleados.

## Características

- Registro de entradas y salidas diarias
- Panel de administración para visualizar los registros
- Consulta de registros por rango de fechas
- Visualización de horas trabajadas por empleado
- Listado de registros del día actual
- Interfaz simple e intuitiva
- Compatible con roles de WordPress
- Exportación de datos básica

## Requisitos

- WordPress 6.0 o superior
- PHP 7.4 o superior
- MySQL 5.6 o superior

## Instalación

1. Descarga el archivo ZIP del plugin
2. Ve al panel de administración de WordPress
3. Navega a Plugins > Añadir nuevo
4. Haz clic en "Subir Plugin" y selecciona el archivo ZIP descargado
5. Activa el plugin una vez instalado

También puedes instalar el plugin manualmente:

1. Descarga y descomprime el archivo del plugin
2. Sube la carpeta `wp-control-acceso` al directorio `/wp-content/plugins/` de tu instalación WordPress
3. Activa el plugin desde el menú 'Plugins' en WordPress

## Estructura del Plugin

```
wp-control-acceso/
├── admin/                 # Archivos de administración
├── includes/             # Clases principales
│   ├── class-wp-control-acceso.php
│   ├── class-wp-control-acceso-activator.php
│   └── class-wp-control-acceso-registros.php
├── public/              # Archivos públicos (CSS, JS)
├── wp-control-acceso.php # Archivo principal del plugin
└── README.md           # Este archivo
```

## Configuración

1. Una vez activado, ve a "Control de Horarios" en el menú de WordPress
2. Los empleados podrán registrar sus entradas y salidas desde el panel de usuario
3. Los administradores pueden ver y exportar los registros desde el panel de administración

## Instrucciones de Uso

### Shortcodes Disponibles

El plugin proporciona tres shortcodes principales que puedes usar en cualquier página o post de WordPress:

1. `[control_acceso_registro]`
   - Muestra el formulario de registro de entrada/salida para los empleados
   - Ideal para colocar en una página dedicada al fichaje
   - Ejemplo: `[control_acceso_registro]`

2. `[control_acceso_dashboard]`
   - Muestra el panel de control con resumen de registros
   - Recomendado para la página principal del sistema de control horario
   - Ejemplo: `[control_acceso_dashboard]`

3. `[control_acceso_mis_reportes]`
   - Muestra los reportes personales del usuario actual
   - Perfecto para que cada empleado vea su historial
   - Ejemplo: `[control_acceso_mis_reportes]`

### Configuración Recomendada

1. Crea tres páginas nuevas en WordPress:
   - "Registro de Horario": Añade el shortcode `[control_acceso_registro]`
   - "Panel de Control": Añade el shortcode `[control_acceso_dashboard]`
   - "Mis Registros": Añade el shortcode `[control_acceso_mis_reportes]`

2. Añade estas páginas al menú de tu sitio para fácil acceso

### Roles y Permisos

- **Empleados**: Pueden acceder al registro de entrada/salida y ver sus propios reportes
- **Administradores**: Tienen acceso completo a todos los registros y funciones administrativas

### Uso Diario

1. **Para Empleados**:
   - Al comenzar la jornada: Acceder a la página de registro y marcar entrada
   - Al finalizar la jornada: Acceder a la página de registro y marcar salida
   - Consultar el historial personal en la página "Mis Registros"

2. **Para Administradores**:
   - Acceder al Panel de Control para ver todos los registros
   - Exportar datos según sea necesario
   - Gestionar los registros de todos los empleados

### Widgets y Sidebar

Para añadir acceso rápido al registro de horario:
1. Ve a Apariencia > Widgets
2. Arrastra un widget de "Texto" a tu sidebar
3. Añade cualquiera de los shortcodes mencionados arriba
4. Guarda los cambios

## Versiones de WordPress Compatibles

- Versión mínima requerida: WordPress 6.0
- Probado hasta: WordPress 6.4
- Compatible con WordPress Multisite

## Soporte

Para reportar problemas o solicitar soporte, por favor:
1. Revisa la documentación incluida
2. Contacta con el soporte técnico

## Licencia

Este plugin está licenciado bajo la GPLv2 o posterior.

## Changelog

### 1.0.0
- Versión inicial del plugin
- Sistema básico de registro de entradas y salidas
- Panel de administración para visualización de registros
- Exportación básica de datos
