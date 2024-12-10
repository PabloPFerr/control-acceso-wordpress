<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1><i class="fas fa-question-circle"></i> Ayuda - Control de Acceso</h1>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-book"></i> Guía de Uso</h2>
        </div>
        <div class="card-body">
            <!-- Shortcodes -->
            <section class="mb-5">
                <h3><i class="fas fa-code"></i> Shortcodes Disponibles</h3>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Shortcode</th>
                                <th>Descripción</th>
                                <th>Uso Recomendado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>[control_acceso_registro]</code></td>
                                <td>
                                    <ul>
                                        <li>Muestra el formulario de registro de entrada/salida</li>
                                        <li>Incluye estado actual del usuario</li>
                                        <li>Botones para registrar entrada y salida</li>
                                    </ul>
                                </td>
                                <td>Colocar en una página dedicada al registro de asistencia</td>
                            </tr>
                            <tr>
                                <td><code>[control_acceso_dashboard]</code></td>
                                <td>
                                    <ul>
                                        <li>Muestra el dashboard personal del usuario</li>
                                        <li>Estadísticas del día actual</li>
                                        <li>Historial de registros del día</li>
                                    </ul>
                                </td>
                                <td>Ideal para la página principal del usuario o área personal</td>
                            </tr>
                            <tr>
                                <td><code>[control_acceso_mis_reportes]</code></td>
                                <td>
                                    <ul>
                                        <li>Sistema de reportes personales</li>
                                        <li>Filtros por fecha</li>
                                        <li>Diferentes tipos de reportes</li>
                                        <li>Exportación a Excel</li>
                                    </ul>
                                </td>
                                <td>Página dedicada a reportes y estadísticas personales</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Configuración Recomendada -->
            <section class="mb-5">
                <h3><i class="fas fa-cogs"></i> Configuración Recomendada</h3>
                <div class="alert alert-info">
                    <h4>Estructura de Páginas Sugerida</h4>
                    <ol>
                        <li>Crear una página "Registro de Asistencia" con el shortcode <code>[control_acceso_registro]</code></li>
                        <li>Crear una página "Mi Dashboard" con el shortcode <code>[control_acceso_dashboard]</code></li>
                        <li>Crear una página "Mis Reportes" con el shortcode <code>[control_acceso_mis_reportes]</code></li>
                    </ol>
                </div>
            </section>

            <!-- Panel de Administración -->
            <section class="mb-5">
                <h3><i class="fas fa-user-shield"></i> Panel de Administración</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Dashboard Admin</h4>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Vista general de todos los usuarios</li>
                                    <li>Estadísticas globales</li>
                                    <li>Registros del día actual</li>
                                    <li>Estado de todos los usuarios</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Reportes Administrativos</h4>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Reportes de todos los usuarios</li>
                                    <li>Filtros avanzados</li>
                                    <li>Exportación a Excel</li>
                                    <li>Estadísticas detalladas</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section class="mb-5">
                <h3><i class="fas fa-question"></i> Preguntas Frecuentes</h3>
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                ¿Cómo agrego los shortcodes a una página?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <ol>
                                    <li>Ve a Pages > Add New en WordPress</li>
                                    <li>Dale un título a tu página</li>
                                    <li>Agrega un bloque "Shortcode"</li>
                                    <li>Pega el shortcode deseado</li>
                                    <li>Publica la página</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                ¿Cómo funciona el registro de horas?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <ul>
                                    <li>El usuario registra su entrada al llegar</li>
                                    <li>El sistema guarda la hora exacta</li>
                                    <li>Al salir, registra su salida</li>
                                    <li>El sistema calcula automáticamente las horas trabajadas</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                ¿Cómo exporto los reportes?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <ol>
                                    <li>Ve a la sección de reportes</li>
                                    <li>Aplica los filtros deseados</li>
                                    <li>Haz clic en "Exportar a Excel"</li>
                                    <li>Se descargará un archivo Excel con los datos</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Soporte -->
            <section>
                <h3><i class="fas fa-life-ring"></i> Soporte</h3>
                <div class="alert alert-success">
                    <h4>¿Necesitas ayuda adicional?</h4>
                    <p>Si tienes dudas o problemas con el plugin, puedes:</p>
                    <ul>
                        <li>Revisar la documentación completa en línea</li>
                        <li>Contactar al soporte técnico</li>
                        <li>Reportar problemas en el repositorio del plugin</li>
                    </ul>
                </div>
            </section>
        </div>
    </div>
</div>
