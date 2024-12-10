jQuery(document).ready(function($) {
    // Agregar el campo de rol de Control de Acceso al formulario de usuario
    var roleField = '<tr class="user-control-acceso-role-wrap">' +
        '<th><label for="control_acceso_role">Rol en Control de Acceso</label></th>' +
        '<td>' +
        '<select name="control_acceso_role" id="control_acceso_role">' +
        '<option value="">-- Seleccionar Rol --</option>' +
        '<option value="control_acceso_user">Usuario Normal</option>' +
        '<option value="control_acceso_auditor">Auditor</option>' +
        '</select>' +
        '<p class="description">Selecciona el rol del usuario para el plugin Control de Acceso.</p>' +
        '</td>' +
        '</tr>';

    // Agregar el campo después del rol de WordPress
    $('.user-role-wrap').after(roleField);

    // Si estamos en la página de edición, cargar el rol actual
    var userId = $('#user_id').val();
    if (userId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_control_acceso_role',
                user_id: userId,
                nonce: wpControlAcceso.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    $('#control_acceso_role').val(response.data);
                }
            }
        });
    }
});
