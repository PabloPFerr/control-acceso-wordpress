<?php
if (!is_user_logged_in()) {
    return '<div class="alert alert-warning">Debes iniciar sesión para ver tus reportes.</div>';
}

// Verificar si el usuario es empleado o administrador
$user = wp_get_current_user();

// Debug: Mostrar información del usuario
$roles_info = print_r($user->roles, true);
$caps_info = print_r($user->allcaps, true);

if (!in_array('empleado', (array) $user->roles) && 
    !in_array('Empleado', (array) $user->roles) && 
    !in_array('administrator', (array) $user->roles)) {
    return '<div class="alert alert-warning">No tienes permiso para ver reportes.<br>
            Roles: ' . $roles_info . '<br>
            Capacidades: ' . $caps_info . '</div>';
}

$user_id = get_current_user_id();
$registros = new WP_Control_Acceso_Registros();

// Procesar filtros
$fecha_inicio = isset($_GET['fecha_inicio']) ? sanitize_text_field($_GET['fecha_inicio']) : date('Y-m-d', strtotime('-30 days'));
$fecha_fin = isset($_GET['fecha_fin']) ? sanitize_text_field($_GET['fecha_fin']) : date('Y-m-d');
$tipo_reporte = isset($_GET['tipo']) ? sanitize_text_field($_GET['tipo']) : 'detallado';
?>

<div class="wp-control-acceso-reportes">
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-filter"></i> Filtros
            </h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <?php foreach ($_GET as $key => $value): ?>
                    <?php if ($key !== 'fecha_inicio' && $key !== 'fecha_fin' && $key !== 'tipo'): ?>
                        <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="col-md-4">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" 
                           value="<?php echo esc_attr($fecha_inicio); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" 
                           value="<?php echo esc_attr($fecha_fin); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Tipo de Reporte</label>
                    <select name="tipo" class="form-control">
                        <option value="detallado" <?php selected($tipo_reporte, 'detallado'); ?>>
                            Registros Detallados
                        </option>
                        <option value="por_dia" <?php selected($tipo_reporte, 'por_dia'); ?>>
                            Horas por Día
                        </option>
                        <option value="totales" <?php selected($tipo_reporte, 'totales'); ?>>
                            Resumen Total
                        </option>
                    </select>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportarMisReportes()">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <?php if ($tipo_reporte === 'detallado'): ?>
                    <i class="fas fa-list"></i> Registros Detallados
                <?php elseif ($tipo_reporte === 'por_dia'): ?>
                    <i class="fas fa-calendar-day"></i> Horas por Día
                <?php else: ?>
                    <i class="fas fa-chart-line"></i> Resumen Total
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
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

            <?php elseif ($tipo_reporte === 'por_dia'): ?>
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
                                    <th><i class="fas fa-calendar"></i> Fecha</th>
                                    <th><i class="fas fa-clock"></i> Horas Trabajadas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registros_por_dia as $registro): ?>
                                <tr>
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

            <?php else: ?>
                <?php
                $registros_totales = $registros->get_horas_totales($fecha_inicio, $fecha_fin);
                if (empty($registros_totales)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No se encontraron registros
                    </div>
                <?php else: ?>
                    <?php 
                    $mis_totales = current(array_filter($registros_totales, function($r) use ($user_id) {
                        return $r->user_id == $user_id;
                    }));
                    ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Días Trabajados</h6>
                                    <h2><?php echo $mis_totales->dias_trabajados; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Horas Totales</h6>
                                    <h2><?php echo number_format($mis_totales->horas_totales, 2); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Promedio Diario</h6>
                                    <h2>
                                        <?php 
                                        $promedio = $mis_totales->dias_trabajados > 0 ? 
                                            $mis_totales->horas_totales / $mis_totales->dias_trabajados : 0;
                                        echo number_format($promedio, 2);
                                        ?>
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportarMisReportes() {
    var url = wpControlAcceso.ajax_url + 
              '?action=wp_control_acceso_exportar' +
              '&tipo=<?php echo $tipo_reporte; ?>' +
              '&fecha_inicio=<?php echo $fecha_inicio; ?>' +
              '&fecha_fin=<?php echo $fecha_fin; ?>' +
              '&user_id=<?php echo $user_id; ?>' +
              '&_wpnonce=' + wpControlAcceso.nonce;
    window.location.href = url;
}
</script>
