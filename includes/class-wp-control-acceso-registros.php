<?php

class WP_Control_Acceso_Registros {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'control_acceso_registros';
    }

    /**
     * Obtener registros del día actual
     */
    public function get_registros_hoy() {
        global $wpdb;
        return $wpdb->get_results("
            SELECT r.*, u.display_name, u.user_email 
            FROM {$this->table_name} r
            JOIN {$wpdb->users} u ON r.user_id = u.ID
            WHERE DATE(r.hora_entrada) = CURDATE()
            ORDER BY r.hora_entrada DESC
        ");
    }

    /**
     * Obtener registros por rango de fechas
     */
    public function get_registros_por_fecha($fecha_inicio, $fecha_fin, $user_id = null) {
        global $wpdb;
        
        $sql = "SELECT r.*, u.display_name, u.user_email 
                FROM {$this->table_name} r
                JOIN {$wpdb->users} u ON r.user_id = u.ID
                WHERE DATE(r.hora_entrada) BETWEEN %s AND %s";
        
        $params = [$fecha_inicio, $fecha_fin];
        
        if ($user_id) {
            $sql .= " AND r.user_id = %d";
            $params[] = $user_id;
        }
        
        $sql .= " ORDER BY r.hora_entrada DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }

    /**
     * Obtener horas por día
     */
    public function get_horas_por_dia($fecha_inicio, $fecha_fin, $user_id = null) {
        global $wpdb;
        
        $sql = "SELECT 
                    DATE(r.hora_entrada) as fecha,
                    u.display_name,
                    u.user_email,
                    SUM(r.duracion) as horas_totales
                FROM {$this->table_name} r
                JOIN {$wpdb->users} u ON r.user_id = u.ID
                WHERE DATE(r.hora_entrada) BETWEEN %s AND %s";
        
        $params = [$fecha_inicio, $fecha_fin];
        
        if ($user_id) {
            $sql .= " AND r.user_id = %d";
            $params[] = $user_id;
        }
        
        $sql .= " GROUP BY DATE(r.hora_entrada), r.user_id
                 ORDER BY fecha DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }

    /**
     * Obtener horas totales
     */
    public function get_horas_totales($fecha_inicio, $fecha_fin) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("
            SELECT 
                u.ID as user_id,
                u.display_name,
                u.user_email,
                COUNT(DISTINCT DATE(r.hora_entrada)) as dias_trabajados,
                SUM(r.duracion) as horas_totales
            FROM {$this->table_name} r
            JOIN {$wpdb->users} u ON r.user_id = u.ID
            WHERE DATE(r.hora_entrada) BETWEEN %s AND %s
            GROUP BY r.user_id
            ORDER BY horas_totales DESC
        ", $fecha_inicio, $fecha_fin));
    }

    /**
     * Registrar entrada
     */
    public function registrar_entrada($user_id) {
        global $wpdb;
        
        // Verificar si ya tiene una entrada sin salida
        $registro_activo = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$this->table_name}
            WHERE user_id = %d AND hora_salida IS NULL
        ", $user_id));

        if ($registro_activo) {
            return new WP_Error('registro_activo', 'Ya tienes un registro activo');
        }

        $resultado = $wpdb->insert(
            $this->table_name,
            array(
                'user_id' => $user_id,
                'hora_entrada' => current_time('mysql')
            ),
            array('%d', '%s')
        );

        if ($resultado === false) {
            return new WP_Error('db_error', 'Error al registrar la entrada');
        }

        return $wpdb->insert_id;
    }

    /**
     * Registrar salida
     */
    public function registrar_salida($user_id) {
        global $wpdb;
        
        // Buscar registro activo
        $registro_activo = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$this->table_name}
            WHERE user_id = %d AND hora_salida IS NULL
        ", $user_id));

        if (!$registro_activo) {
            return new WP_Error('no_registro', 'No tienes un registro activo');
        }

        $hora_salida = current_time('mysql');
        $duracion = (strtotime($hora_salida) - strtotime($registro_activo->hora_entrada)) / 3600;

        $resultado = $wpdb->update(
            $this->table_name,
            array(
                'hora_salida' => $hora_salida,
                'duracion' => $duracion
            ),
            array('id' => $registro_activo->id),
            array('%s', '%f'),
            array('%d')
        );

        if ($resultado === false) {
            return new WP_Error('db_error', 'Error al registrar la salida');
        }

        return true;
    }

    /**
     * Exportar a Excel
     */
    public function exportar_excel($tipo, $fecha_inicio, $fecha_fin, $user_id = null) {
        require_once WP_CONTROL_ACCESO_PLUGIN_DIR . 'vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        switch ($tipo) {
            case 'detallado':
                $registros = $this->get_registros_por_fecha($fecha_inicio, $fecha_fin, $user_id);
                $this->generar_excel_detallado($sheet, $registros);
                break;
            case 'por_dia':
                $registros = $this->get_horas_por_dia($fecha_inicio, $fecha_fin, $user_id);
                $this->generar_excel_por_dia($sheet, $registros);
                break;
            case 'totales':
                $registros = $this->get_horas_totales($fecha_inicio, $fecha_fin);
                $this->generar_excel_totales($sheet, $registros);
                break;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $filename = 'reporte_' . $tipo . '_' . date('Y-m-d') . '.xlsx';
        $filepath = WP_CONTROL_ACCESO_PLUGIN_DIR . 'temp/' . $filename;
        
        $writer->save($filepath);
        
        return $filepath;
    }

    private function generar_excel_detallado($sheet, $registros) {
        $sheet->setCellValue('A1', 'Usuario');
        $sheet->setCellValue('B1', 'Fecha');
        $sheet->setCellValue('C1', 'Entrada');
        $sheet->setCellValue('D1', 'Salida');
        $sheet->setCellValue('E1', 'Duración (horas)');

        $row = 2;
        foreach ($registros as $registro) {
            $sheet->setCellValue('A' . $row, $registro->display_name);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($registro->hora_entrada)));
            $sheet->setCellValue('C' . $row, date('H:i:s', strtotime($registro->hora_entrada)));
            $sheet->setCellValue('D' . $row, $registro->hora_salida ? date('H:i:s', strtotime($registro->hora_salida)) : '-');
            $sheet->setCellValue('E' . $row, $registro->duracion ?? '-');
            $row++;
        }
    }

    private function generar_excel_por_dia($sheet, $registros) {
        $sheet->setCellValue('A1', 'Fecha');
        $sheet->setCellValue('B1', 'Usuario');
        $sheet->setCellValue('C1', 'Horas Totales');

        $row = 2;
        foreach ($registros as $registro) {
            $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($registro->fecha)));
            $sheet->setCellValue('B' . $row, $registro->display_name);
            $sheet->setCellValue('C' . $row, number_format($registro->horas_totales, 2));
            $row++;
        }
    }

    private function generar_excel_totales($sheet, $registros) {
        $sheet->setCellValue('A1', 'Usuario');
        $sheet->setCellValue('B1', 'Días Trabajados');
        $sheet->setCellValue('C1', 'Horas Totales');
        $sheet->setCellValue('D1', 'Promedio Diario');

        $row = 2;
        foreach ($registros as $registro) {
            $promedio = $registro->dias_trabajados > 0 ? 
                       $registro->horas_totales / $registro->dias_trabajados : 0;

            $sheet->setCellValue('A' . $row, $registro->display_name);
            $sheet->setCellValue('B' . $row, $registro->dias_trabajados);
            $sheet->setCellValue('C' . $row, number_format($registro->horas_totales, 2));
            $sheet->setCellValue('D' . $row, number_format($promedio, 2));
            $row++;
        }
    }
}
