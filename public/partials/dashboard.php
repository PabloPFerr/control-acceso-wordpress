<?php
$user = wp_get_current_user();
?>

<div class="wp-control-acceso-dashboard">
    <!-- Estado actual -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <?php echo get_avatar($user->ID, 48, '', '', array('class' => 'rounded-circle me-3')); ?>
                    <div>
                        <h5 class="mb-1"><?php echo esc_html($user->display_name); ?></h5>
                        <p class="text-muted mb-0">
                            <?php if ($registro_activo): ?>
                                <span class="text-success">
                                    <i class="fas fa-circle"></i> En el trabajo
                                </span>
                            <?php else: ?>
                                <span class="text-secondary">
                                    <i class="fas fa-circle"></i> Fuera del trabajo
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div>
                    <?php if ($registro_activo): ?>
                        <button class="btn btn-danger" onclick="registrarSalida()">
                            <i class="fas fa-sign-out-alt"></i> Registrar Salida
                        </button>
                    <?php else: ?>
                        <button class="btn btn-primary" onclick="registrarEntrada()">
                            <i class="fas fa-sign-in-alt"></i> Registrar Entrada
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas del día -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-3">
                        <i class="fas fa-clock"></i> Horas Trabajadas Hoy
                    </h6>
                    <h3>
                        <?php
                        $horas = !empty($horas_hoy) ? number_format($horas_hoy[0]->horas_totales, 2) : '0.00';
                        echo $horas . ' horas';
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-3">
                        <i class="fas fa-list"></i> Registros de Hoy
                    </h6>
                    <h3><?php echo count($registros_hoy); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Registros del día -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-history"></i> Historial de Hoy
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($registros_hoy)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay registros para el día de hoy
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-clock"></i> Entrada</th>
                                <th><i class="fas fa-clock"></i> Salida</th>
                                <th><i class="fas fa-hourglass-half"></i> Duración</th>
                                <th><i class="fas fa-circle"></i> Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros_hoy as $registro): ?>
                            <tr>
                                <td><?php echo date('H:i:s', strtotime($registro->hora_entrada)); ?></td>
                                <td>
                                    <?php 
                                    if ($registro->hora_salida) {
                                        echo date('H:i:s', strtotime($registro->hora_salida));
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
        </div>
    </div>
</div>

<script>
function registrarEntrada() {
    jQuery.ajax({
        url: wpControlAcceso.ajax_url,
        type: 'POST',
        data: {
            action: 'wp_control_acceso_entrada',
            nonce: wpControlAcceso.nonce
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data);
            }
        }
    });
}

function registrarSalida() {
    jQuery.ajax({
        url: wpControlAcceso.ajax_url,
        type: 'POST',
        data: {
            action: 'wp_control_acceso_salida',
            nonce: wpControlAcceso.nonce
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data);
            }
        }
    });
}
</script>
