<?php
// Verificar que el usuario tiene permisos
if (!current_user_can('manage_options')) {
    return;
}

$registros = new WP_Control_Acceso_Registros();

// Procesar filtros
$fecha_inicio = isset($_GET['fecha_inicio']) ? sanitize_text_field($_GET['fecha_inicio']) : date('Y-m-d');
$fecha_fin = isset($_GET['fecha_fin']) ? sanitize_text_field($_GET['fecha_fin']) : date('Y-m-d');
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// Obtener datos según el tipo de reporte
$tipo_reporte = isset($_GET['tipo']) ? sanitize_text_field($_GET['tipo']) : 'detallado';

// Obtener los datos para los gráficos
$datos_grafico = $registros->get_horas_por_dia($fecha_inicio, $fecha_fin, $user_id);
$labels = array();
$horas = array();
foreach ($datos_grafico as $dato) {
    $labels[] = $dato->fecha;
    $horas[] = $dato->horas_totales;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?php echo admin_url('admin.php'); ?>" class="row g-3">
                <input type="hidden" name="page" value="wp-control-acceso-reports">
                
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" 
                           value="<?php echo esc_attr($fecha_inicio); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" 
                           value="<?php echo esc_attr($fecha_fin); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Usuario</label>
                    <?php
                    wp_dropdown_users(array(
                        'name' => 'user_id',
                        'class' => 'form-control',
                        'show_option_all' => 'Todos los usuarios',
                        'selected' => $user_id
                    ));
                    ?>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Tipo de Reporte</label>
                    <select name="tipo" class="form-control">
                        <option value="detallado" <?php selected($tipo_reporte, 'detallado'); ?>>
                            Detallado
                        </option>
                        <option value="por_dia" <?php selected($tipo_reporte, 'por_dia'); ?>>
                            Por Día
                        </option>
                        <option value="totales" <?php selected($tipo_reporte, 'totales'); ?>>
                            Totales
                        </option>
                    </select>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportarExcel()">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs de reportes -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo $tipo_reporte === 'detallado' ? 'active' : ''; ?>" 
                       href="?page=wp-control-acceso-reports&tipo=detallado">
                        <i class="fas fa-list"></i> Registros Detallados
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $tipo_reporte === 'por_dia' ? 'active' : ''; ?>" 
                       href="?page=wp-control-acceso-reports&tipo=por_dia">
                        <i class="fas fa-calendar-day"></i> Horas por Día
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $tipo_reporte === 'totales' ? 'active' : ''; ?>" 
                       href="?page=wp-control-acceso-reports&tipo=totales">
                        <i class="fas fa-clock"></i> Horas Totales
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content mt-3">
                <?php if ($tipo_reporte === 'detallado'): ?>
                    <?php
                    $registros_detallados = $registros->get_registros_por_fecha($fecha_inicio, $fecha_fin, $user_id);
                    if (empty($registros_detallados)): ?>
                        <div class="alert alert-info">No hay registros para mostrar.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Duración</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros_detallados as $registro): ?>
                                        <tr>
                                            <td><?php echo esc_html($registro->display_name); ?></td>
                                            <td><?php echo esc_html($registro->hora_entrada); ?></td>
                                            <td><?php echo $registro->hora_salida ? esc_html($registro->hora_salida) : 'En curso'; ?></td>
                                            <td><?php echo $registro->duracion ? number_format($registro->duracion, 2) . ' horas' : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($tipo_reporte === 'por_dia'): ?>
                    <?php
                    $registros_por_dia = $registros->get_horas_por_dia($fecha_inicio, $fecha_fin, $user_id);
                    if (empty($registros_por_dia)): ?>
                        <div class="alert alert-info">No hay registros para mostrar.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th>Horas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros_por_dia as $registro): ?>
                                        <tr>
                                            <td><?php echo esc_html($registro->fecha); ?></td>
                                            <td><?php echo esc_html($registro->display_name); ?></td>
                                            <td><?php echo number_format($registro->horas_totales, 2); ?> horas</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($tipo_reporte === 'totales'): ?>
                    <?php
                    $registros_totales = $registros->get_horas_totales($fecha_inicio, $fecha_fin);
                    if (empty($registros_totales)): ?>
                        <div class="alert alert-info">No hay registros para mostrar.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Días Trabajados</th>
                                        <th>Horas Totales</th>
                                        <th>Promedio Diario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros_totales as $registro): ?>
                                        <tr>
                                            <td><?php echo esc_html($registro->display_name); ?></td>
                                            <td><?php echo esc_html($registro->dias_trabajados); ?></td>
                                            <td><?php echo number_format($registro->horas_totales, 2); ?> horas</td>
                                            <td><?php echo number_format($registro->horas_totales / $registro->dias_trabajados, 2); ?> horas</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mt-4">
        <!-- Gráfico de horas por día -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Horas por Día</h5>
                </div>
                <div class="card-body grafico-container">
                    <canvas id="graficoHorasDia"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de totales por usuario -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Horas Totales por Usuario</h5>
                </div>
                <div class="card-body grafico-container">
                    <canvas id="graficoTotalesUsuario"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Configuración de los gráficos
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de horas por día
    var ctxDia = document.getElementById('graficoHorasDia').getContext('2d');
    new Chart(ctxDia, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Horas',
                data: <?php echo json_encode($horas); ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Horas Trabajadas por Día'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Horas'
                    }
                }
            }
        }
    });

    // Gráfico de totales por usuario
    <?php
    $datos_totales = $registros->get_horas_totales($fecha_inicio, $fecha_fin);
    $usuarios = array();
    $horas_totales = array();
    foreach ($datos_totales as $dato) {
        $usuarios[] = $dato->display_name;
        $horas_totales[] = $dato->horas_totales;
    }
    ?>
    var ctxTotal = document.getElementById('graficoTotalesUsuario').getContext('2d');
    new Chart(ctxTotal, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($usuarios); ?>,
            datasets: [{
                label: 'Horas Totales',
                data: <?php echo json_encode($horas_totales); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Horas Totales por Usuario'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Horas'
                    }
                }
            }
        }
    });
});

function exportarExcel() {
    var url = ajaxurl + '?action=wp_control_acceso_exportar&tipo=<?php echo $tipo_reporte; ?>' +
        '&fecha_inicio=<?php echo $fecha_inicio; ?>' +
        '&fecha_fin=<?php echo $fecha_fin; ?>' +
        '&user_id=<?php echo $user_id; ?>' +
        '&_wpnonce=' + wpControlAcceso.nonce;
    window.location.href = url;
}
</script>

<style>
.card {
    margin-bottom: 2rem;
}
.card-body {
    padding: 1.5rem;
}
.table {
    margin-bottom: 0;
}
.grafico-container {
    height: 300px;
    position: relative;
}
canvas {
    max-height: 100%;
    width: 100% !important;
}
</style>