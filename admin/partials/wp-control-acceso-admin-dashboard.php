<?php
// Verificar que el usuario tiene permisos
if (!current_user_can('manage_options')) {
    return;
}

// Obtener estadísticas
global $wpdb;
$total_usuarios = count_users();
$usuarios_activos = $wpdb->get_var("
    SELECT COUNT(DISTINCT user_id) 
    FROM {$wpdb->prefix}control_acceso_registros 
    WHERE hora_entrada >= CURDATE() AND hora_salida IS NULL
");
$registros_hoy = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->prefix}control_acceso_registros 
    WHERE DATE(hora_entrada) = CURDATE()
");

// Obtener registros del día
$registros = $wpdb->get_results("
    SELECT r.*, u.display_name 
    FROM {$wpdb->prefix}control_acceso_registros r
    JOIN {$wpdb->users} u ON r.user_id = u.ID
    WHERE DATE(r.hora_entrada) = CURDATE()
    ORDER BY r.hora_entrada DESC
");
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Tarjetas de estadísticas -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-users"></i> Total Usuarios
                    </h5>
                    <h2><?php echo $total_usuarios['total_users']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-user-check"></i> Usuarios Activos
                    </h5>
                    <h2><?php echo $usuarios_activos; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-clock"></i> Registros Hoy
                    </h5>
                    <h2><?php echo $registros_hoy; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de registros -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> Registros de Hoy
            </h5>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Usuario</th>
                        <th><i class="fas fa-clock"></i> Entrada</th>
                        <th><i class="fas fa-clock"></i> Salida</th>
                        <th><i class="fas fa-hourglass-half"></i> Duración</th>
                        <th><i class="fas fa-circle"></i> Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $registro): ?>
                    <tr>
                        <td><?php echo esc_html($registro->display_name); ?></td>
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
    </div>
</div>
