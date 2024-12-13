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

### Instalación desde Git

1. Clona este repositorio
2. Renombra el directorio a `wp-control-acceso`
3. Sube el directorio completo a la carpeta `/wp-content/plugins/` de tu instalación WordPress
4. Activa el plugin desde el panel de administración de WordPress

### Instalación Manual desde ZIP

1. Descarga el código como ZIP desde GitHub
2. Descomprime el archivo
3. Renombra el directorio descomprimido a `wp-control-acceso`
4. Sube el directorio completo a la carpeta `/wp-content/plugins/` de tu instalación WordPress
5. Activa el plugin desde el panel de administración de WordPress

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

## Roles y Permisos

El plugin crea tres roles específicos:

### Empleado
- Puede registrar su entrada y salida
- Puede ver sus propios registros de asistencia

### Supervisor
- Puede ver y exportar todos los reportes
- Puede gestionar usuarios
- Puede modificar configuraciones del sistema

### Auditor
- Puede ver y exportar reportes
- No puede registrar asistencia
- No puede gestionar usuarios ni configuraciones

## Instrucciones de Uso

### Para Empleados
1. Inicia sesión en WordPress con tu cuenta de empleado
2. Accede a la página de registro de asistencia
3. Registra tu entrada/salida según corresponda

### Para Supervisores
1. Accede al panel de administración
2. Gestiona usuarios y sus roles
3. Configura las opciones del sistema
4. Visualiza y exporta reportes

### Para Auditores
1. Accede al panel de administración
2. Visualiza y exporta reportes de asistencia

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

## Cierre Automático de Registros

El plugin incluye una funcionalidad de cierre automático de registros que se ejecuta diariamente a medianoche. Esta función:

- Cierra automáticamente todos los registros que no tienen hora de salida registrada
- Establece la hora de salida a las 23:59:59 del día del registro
- Marca el registro como "cierre automático" para diferenciarlo de los cierres manuales

### Configuración del Cron

El cron se configura automáticamente al activar el plugin, pero para asegurar su correcto funcionamiento:

1. Asegúrate de que el cron de WordPress está funcionando correctamente:
   ```bash
   wp cron event list
   ```
   Deberías ver el evento `wp_control_acceso_cierre_automatico` programado para la próxima medianoche.

2. Si el cron no aparece o necesitas reprogramarlo:
   ```bash
   wp cron event unschedule wp_control_acceso_cierre_automatico
   wp plugin deactivate wp-control-acceso
   wp plugin activate wp-control-acceso
   ```

3. Para un funcionamiento óptimo, configura un cron real en tu servidor:
   ```bash
   # Ejecutar a las 00:01 todos los días
   1 0 * * * cd /path/to/wordpress && wp cron event run --due-now >/dev/null 2>&1
   ```
   
   Este comando:
   - Se ejecuta al minuto 1 (1)
   - De la hora 0 (medianoche) (0)
   - Todos los días del mes (*)
   - Todos los meses (*)
   - Todos los días de la semana (*)

### Visualización de Registros Automáticos

Los registros cerrados automáticamente se identifican en todas las vistas con un ícono de robot (🤖) junto a la hora de salida.

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
