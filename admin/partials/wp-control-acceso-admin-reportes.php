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
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?php echo admin_url('admin.php'); ?>" class="row g-3">
                <input type="hidden" name="page" value="wp-control-acceso-reportes">
                
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
                       href="?page=wp-control-acceso-reportes&tipo=detallado">
                        <i class="fas fa-list"></i> Registros Detallados
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $tipo_reporte === 'por_dia' ? 'active' : ''; ?>" 
                       href="?page=wp-control-acceso-reportes&tipo=por_dia">
                        <i class="fas fa-calendar-day"></i> Horas por Día
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $tipo_reporte === 'totales' ? 'active' : ''; ?>" 
                       href="?page=wp-control-acceso-reportes&tipo=totales">
                        <i class="fas fa-clock"></i> Horas Totales
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <?php if ($tipo_reporte === 'detallado'): ?>
                    <?php
                    $registros_detallados = $registros->get_registros_por_fecha($fecha_inicio, $fecha_fin, $user_id);
                    if (empty($registros_detallados)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No se encontraron registros
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user"></i> Usuario</th>
                                        <th><i class="fas fa-calendar"></i> Fecha</th>
                                        <th><i class="fas fa-clock"></i> Entrada</th>
                                        <th><i class="fas fa-clock"></i> Salida</th>
                                        <th><i class="fas fa-hourglass-half"></i> Duración</th>
                                        <th><i class="fas fa-circle"></i> Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros_detallados as $registro): ?>
                                    <tr>
                                        <td><?php echo esc_html($registro->display_name); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($registro->hora_entrada)); ?></td>
                                        <td><?php echo date('H:i:s', strtotime($registro->hora_entrada)); ?></td>
                                        <td>
                                            <?php 
                                            if ($registro->hora_salida) {
                                                echo date('H:i:s', strtotime($registro->hora_salida));
                                                if ($registro->cierre_automatico) {
                                                    echo ' <span class="badge bg-warning" title="Cierre automático"><i class="fas fa-robot"></i></span>';
                                                }
                                            } else {
                                                echo '<span class="badge bg-warning">En curso</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($registro->duracion) {
                                                echo '<span class="badge bg-info">' . 
                                                    number_format($registro->duracion, 2) . ' horas</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">-</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (!$registro->hora_salida): ?>
                                                <span class="badge bg-primary">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Finalizado</span>
                                            <?php endif; ?>
                                        </td>
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
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No se encontraron registros
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user"></i> Usuario</th>
                                        <th><i class="fas fa-calendar"></i> Fecha</th>
                                        <th><i class="fas fa-clock"></i> Horas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros_por_dia as $registro): ?>
                                    <tr>
                                        <td><?php echo esc_html($registro->display_name); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($registro->fecha)); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo number_format($registro->horas_totales, 2); ?> horas
                                            </span>
                                        </td>
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
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No se encontraron registros
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user"></i> Usuario</th>
                                        <th><i class="fas fa-calendar-check"></i> Días Trabajados</th>
                                        <th><i class="fas fa-clock"></i> Horas Totales</th>
                                        <th><i class="fas fa-chart-line"></i> Promedio Diario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros_totales as $registro): ?>
                                    <tr>
                                        <td><?php echo esc_html($registro->display_name); ?></td>
                                        <td><?php echo $registro->dias_trabajados; ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo number_format($registro->horas_totales, 2); ?> horas
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php 
                                                $promedio = $registro->dias_trabajados > 0 ? 
                                                    $registro->horas_totales / $registro->dias_trabajados : 0;
                                                echo number_format($promedio, 2); 
                                                ?> horas/día
                                            </span>
                                        </td>
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
</div>

<script>
function exportarExcel() {
    var url = ajaxurl + '?action=wp_control_acceso_exportar&tipo=<?php echo $tipo_reporte; ?>' +
              '&fecha_inicio=<?php echo $fecha_inicio; ?>' +
              '&fecha_fin=<?php echo $fecha_fin; ?>' +
              '&user_id=<?php echo $user_id; ?>' +
              '&_wpnonce=<?php echo wp_create_nonce('wp_control_acceso_exportar'); ?>';
    window.location.href = url;
}
</script>
