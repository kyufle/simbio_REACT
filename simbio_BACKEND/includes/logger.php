<?php
/**
 * Sistema simple de logging con funciones
 * Los logs se guardan en /logs/YYYY-MM-DD.txt
 */

if (!function_exists('ensure_log_dir')) {
    /**
     * Asegura que existe el directorio de logs
     */
    function ensure_log_dir() {
        $logDir = __DIR__ . '/../logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        return $logDir;
    }
}

if (!function_exists('get_log_file')) {
    /**
     * Obtiene la ruta del archivo de log del día actual
     */
    function get_log_file() {
        $logDir = ensure_log_dir();
        return $logDir . date('Y-m-d') . '.txt';
    }
}

if (!function_exists('simbio_get_client_ip')) {
    /**
     * Obtiene la IP del cliente
     */
    function simbio_get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = 'DESCONOCIDA';
        }
        return trim($ip);
    }
}

if (!function_exists('simbio_get_current_user')) {
    /**
     * Obtiene el usuario actual o "ANONIMO"
     */
    function simbio_get_current_user() {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']['email'])) {
            return $_SESSION['user']['email'];
        }
        return 'ANONIMO';
    }
}

if (!function_exists('write_log')) {
    /**
     * Escribe un mensaje en el log del día
     */
    function write_log($nivel, $mensaje, $datos_adicionales = null) {
        try {
            $archivo = get_log_file();
            $timestamp = date('Y-m-d H:i:s');
            $ip = simbio_get_client_ip();
            $usuario = simbio_get_current_user();
            $metodo = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
            $uri = $_SERVER['REQUEST_URI'] ?? 'CLI';
            
            $linea = "[{$timestamp}] [{$nivel}] [IP: {$ip}] [Usuario: {$usuario}] [{$metodo} {$uri}] {$mensaje}";
            
            if ($datos_adicionales !== null) {
                if (is_array($datos_adicionales)) {
                    $linea .= " | " . json_encode($datos_adicionales, JSON_UNESCAPED_UNICODE);
                } else {
                    $linea .= " | " . $datos_adicionales;
                }
            }
            
            $linea .= "\n";
            
            file_put_contents($archivo, $linea, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Silenciosamente falla si hay error
            error_log("Error escribiendo log: " . $e->getMessage());
        }
    }
}

if (!function_exists('log_info')) {
    /**
     * Log de información (operaciones normales)
     */
    function log_info($mensaje, $datos = null) {
        write_log('INFO', $mensaje, $datos);
    }
}

if (!function_exists('log_warning')) {
    /**
     * Log de advertencia (situaciones inusuales)
     */
    function log_warning($mensaje, $datos = null) {
        write_log('WARNING', $mensaje, $datos);
    }
}

if (!function_exists('log_error')) {
    /**
     * Log de error (fallos importantes)
     */
    function log_error($mensaje, $datos = null) {
        write_log('ERROR', $mensaje, $datos);
    }
}

if (!function_exists('log_debug')) {
    /**
     * Log de debug (solo información de desarrollo)
     */
    function log_debug($mensaje, $datos = null) {
        write_log('DEBUG', $mensaje, $datos);
    }
}

if (!function_exists('log_auth')) {
    /**
     * Log de autenticación
     */
    function log_auth($accion, $email, $exitoso = true, $razon = null) {
        $mensaje = "AUTH [{$accion}] - Email: {$email}";
        if (!$exitoso && $razon) {
            $mensaje .= " - Razón: {$razon}";
        }
        $nivel = $exitoso ? 'INFO' : 'WARNING';
        write_log($nivel, $mensaje);
    }
}

if (!function_exists('get_today_logs')) {
    /**
     * Obtiene los logs del día actual
     */
    function get_today_logs() {
        $archivo = get_log_file();
        if (file_exists($archivo)) {
            return file_get_contents($archivo);
        }
        return "No hay logs para hoy";
    }
}

if (!function_exists('list_logs')) {
    /**
     * Lista todos los archivos de log disponibles
     */
    function list_logs() {
        $logDir = ensure_log_dir();
        $files = glob($logDir . '*.txt');
        return array_map('basename', $files);
    }
}

?>


