<?php
// Verificar que el usuario tiene permisos
if (!current_user_can('control_acceso_manage_users')) {
    return;
}

// Obtener todos los usuarios con roles relevantes
$users = get_users(array(
    'role__in' => array('empleado', 'supervisor_acceso', 'auditor_acceso')
));

// Procesar cambios de rol si se envía el formulario
if (isset($_POST['submit_role_changes']) && check_admin_referer('control_acceso_users_update')) {
    if (isset($_POST['user_roles']) && is_array($_POST['user_roles'])) {
        foreach ($_POST['user_roles'] as $user_id => $new_role) {
            $user = get_user_by('id', $user_id);
            if ($user) {
                // Eliminar roles anteriores relacionados con control de acceso
                $user->remove_role('empleado');
                $user->remove_role('supervisor_acceso');
                $user->remove_role('auditor_acceso');
                
                // Asignar nuevo rol si se seleccionó uno
                if (!empty($new_role)) {
                    $user->add_role($new_role);
                }
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p>Roles actualizados correctamente.</p></div>';
    }
}
?>

<div class="wrap">
    <h1>Gestión de Usuarios de Control de Acceso</h1>
    <p>Gestiona los empleados, supervisores y auditores del sistema de control de acceso.</p>

    <div class="tablenav top">
        <div class="alignleft actions">
            <a href="<?php echo admin_url('user-new.php'); ?>" class="button button-primary">Añadir Nuevo Usuario</a>
        </div>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field('control_acceso_users_update'); ?>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol Actual</th>
                    <th>Cambiar Rol</th>
                    <th>Último Acceso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Obtener todos los usuarios
                $all_users = get_users();
                
                if (!empty($all_users)): 
                    foreach ($all_users as $user):
                        // Obtener roles del usuario
                        $user_roles = $user->roles;
                        $is_empleado = in_array('empleado', $user_roles);
                        $is_supervisor = in_array('supervisor_acceso', $user_roles);
                        $is_auditor = in_array('auditor_acceso', $user_roles);
                        
                        // Solo mostrar si tiene un rol relevante o no tiene ningún rol
                        if ($is_empleado || $is_supervisor || $is_auditor || empty($user_roles)):
                            $last_login = get_user_meta($user->ID, 'last_login', true);
                ?>
                    <tr>
                        <td><?php echo esc_html($user->display_name); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td>
                            <?php 
                            if ($is_empleado) echo 'Empleado';
                            elseif ($is_supervisor) echo 'Supervisor';
                            elseif ($is_auditor) echo 'Auditor';
                            else echo 'Sin rol';
                            ?>
                        </td>
                        <td>
                            <select name="user_roles[<?php echo $user->ID; ?>]">
                                <option value="">-- Sin rol --</option>
                                <option value="empleado" <?php selected($is_empleado); ?>>Empleado</option>
                                <option value="supervisor_acceso" <?php selected($is_supervisor); ?>>Supervisor</option>
                                <option value="auditor_acceso" <?php selected($is_auditor); ?>>Auditor</option>
                            </select>
                        </td>
                        <td><?php echo $last_login ? date('Y-m-d H:i:s', strtotime($last_login)) : 'Nunca'; ?></td>
                        <td>
                            <a href="<?php echo admin_url('user-edit.php?user_id=' . $user->ID); ?>" 
                               class="button button-small">
                                Editar
                            </a>
                        </td>
                    </tr>
                <?php 
                        endif;
                    endforeach; 
                else: 
                ?>
                    <tr>
                        <td colspan="6">No hay usuarios registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="submit_role_changes" class="button button-primary" value="Guardar Cambios">
        </p>
    </form>
</div>
