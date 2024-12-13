<?php
$user = wp_get_current_user();

// Obtener el registro activo directamente
global $wpdb;
$table_name = $wpdb->prefix . 'control_acceso_registros';
$registro_activo = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM {$table_name}
    WHERE user_id = %d 
    AND hora_salida IS NULL
    AND DATE(hora_entrada) = CURDATE()
    ORDER BY hora_entrada DESC
    LIMIT 1
", $user->ID));
?>

<div class="wp-control-acceso-registro">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-clock"></i> Control de Asistencia
            </h5>
        </div>
        <div class="card-body">
            <div class="user-info mb-4">
                <div class="d-flex align-items-center">
                    <div class="avatar me-3">
                        <?php echo get_avatar($user->ID, 64, '', '', array('class' => 'rounded-circle')); ?>
                    </div>
                    <div>
                        <h5 class="mb-1"><?php echo esc_html($user->display_name); ?></h5>
                        <p class="text-muted mb-0"><?php echo esc_html($user->user_email); ?></p>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <?php if ($registro_activo): ?>
                    <div class="mb-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Entrada registrada a las <?php echo date('H:i:s', strtotime($registro_activo->hora_entrada)); ?>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-lg" onclick="registrarSalida()">
                        <i class="fas fa-sign-out-alt"></i> Registrar Salida
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-primary btn-lg" onclick="registrarEntrada()">
                        <i class="fas fa-sign-in-alt"></i> Registrar Entrada
                    </button>
                <?php endif; ?>
            </div>

            <!-- Mensaje de respuesta -->
            <div id="mensaje-respuesta" class="mt-3" style="display: none;"></div>
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
                mostrarMensaje('success', response.data);
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                mostrarMensaje('danger', response.data);
            }
        },
        error: function() {
            mostrarMensaje('danger', 'Error al procesar la solicitud');
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
                mostrarMensaje('success', response.data);
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                mostrarMensaje('danger', response.data);
            }
        },
        error: function() {
            mostrarMensaje('danger', 'Error al procesar la solicitud');
        }
    });
}

function mostrarMensaje(tipo, mensaje) {
    var mensajeDiv = jQuery('#mensaje-respuesta');
    mensajeDiv.html('<div class="alert alert-' + tipo + '">' + mensaje + '</div>');
    mensajeDiv.show();
}
</script>
