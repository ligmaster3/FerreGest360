<?php

// ============================================================================
// Dependencia Principal
// ============================================================================
require_once __DIR__ . '/../config/connection.php';

// Conexión PDO
if (!function_exists('getDBConnection')) {
    function getDBConnection()
    {
        $host = 'localhost';
        $dbname = 'ferregest360';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            return new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die('Error de conexión: ' . $e->getMessage());
        }
    }
}

// Ejecutar consulta preparada
if (!function_exists('ejecutarConsulta')) {
    function ejecutarConsulta($sql, $params = [])
    {
        $conn = getDBConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

// ============================================================================
// CONFIGURACIONES Y CONSTANTES DE OPTIMIZACIÓN
// ============================================================================

define('CACHE_ENABLED', true);
define('CACHE_DURATION', 300);
define('CACHE_DIR', __DIR__ . '/../cache/');
define('ITEMS_PER_PAGE', 20);
define('MAX_SEARCH_RESULTS', 50);
define('MAX_DASHBOARD_ITEMS', 10);
define('MAX_NOMBRE_LENGTH', 200);
define('MAX_DESCRIPCION_LENGTH', 1000);
define('MAX_OBSERVACIONES_LENGTH', 500);
define('MAX_EMAIL_LENGTH', 100);
define('MAX_TELEFONO_LENGTH', 20);
define('MAX_RUC_LENGTH', 20);
define('PATTERN_TELEFONO_PANAMA', '/^507-\d{3}-\d{4}$/');
define('PATTERN_RUC_PANAMA', '/^\d{8}-\d{1}-\d{6}$/');
define('PATTERN_CEDULA_PANAMA', '/^\d{1}-\d{3}-\d{3}$/');
define('PATTERN_EMAIL', '/^[^\s@]+@[^\s@]+\.[^\s@]+$/');
define('PATTERN_URL', '/^https?:\/\/.+/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);
define('SESSION_TIMEOUT', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);
define('MIN_PASSWORD_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('STOCK_ALERT_ENABLED', true);
define('STOCK_ALERT_PERCENTAGE', 20);
define('FACTURA_VENCIDA_ALERT_DAYS', 7);
define('REPORT_MAX_ROWS', 1000);
define('REPORT_DEFAULT_PERIOD', 30);
define('REPORT_CACHE_DURATION', 1800);
define('BACKUP_ENABLED', true);
define('BACKUP_RETENTION_DAYS', 30);
define('BACKUP_TIME', '02:00');

// ============================================================================
// FUNCIONES DE OPTIMIZACIÓN
// ============================================================================

function limpiarCache($tipo = 'all')
{
    if (!CACHE_ENABLED) return false;
    $cache_dir = CACHE_DIR;
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    $files = ($tipo === 'all') ? glob($cache_dir . '*.cache') : glob($cache_dir . $tipo . '*.cache');
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
    return true;
}

function obtenerCache($clave)
{
    if (!CACHE_ENABLED) return false;
    $cache_file = CACHE_DIR . md5($clave) . '.cache';
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < CACHE_DURATION) {
        return unserialize(file_get_contents($cache_file));
    }
    return false;
}

function guardarCache($clave, $datos)
{
    if (!CACHE_ENABLED) return false;
    $cache_dir = CACHE_DIR;
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    $cache_file = $cache_dir . md5($clave) . '.cache';
    return file_put_contents($cache_file, serialize($datos));
}

function validarTelefonoPanama($telefono)
{
    return preg_match(PATTERN_TELEFONO_PANAMA, $telefono);
}

function validarRucPanama($ruc)
{
    return preg_match(PATTERN_RUC_PANAMA, $ruc);
}

function validarCedulaPanama($cedula)
{
    return preg_match(PATTERN_CEDULA_PANAMA, $cedula);
}

function validarEmail($email)
{
    return preg_match(PATTERN_EMAIL, $email);
}

function validarURL($url)
{
    return preg_match(PATTERN_URL, $url);
}

function sanitizarTexto($texto, $max_length = MAX_NOMBRE_LENGTH)
{
    $texto = trim($texto);
    $texto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    $texto = substr($texto, 0, $max_length);
    return $texto;
}

function validarPassword($password)
{
    if (strlen($password) < MIN_PASSWORD_LENGTH) return false;
    if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) return false;
    if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) return false;
    if (PASSWORD_REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) return false;
    if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $password)) return false;
    return true;
}

function generarPasswordSegura($length = 12)
{
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $special = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    $password = '';
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    $password .= $numbers[rand(0, strlen($numbers) - 1)];
    $password .= $special[rand(0, strlen($special) - 1)];
    $all_chars = $uppercase . $lowercase . $numbers . $special;
    for ($i = 4; $i < $length; $i++) {
        $password .= $all_chars[rand(0, strlen($all_chars) - 1)];
    }
    return str_shuffle($password);
}

function formatearTelefono($telefono)
{
    $telefono = preg_replace('/[^0-9]/', '', $telefono);
    if (strlen($telefono) === 10) {
        return '507-' . substr($telefono, 0, 3) . '-' . substr($telefono, 3);
    }
    return $telefono;
}

function formatearRuc($ruc)
{
    $ruc = preg_replace('/[^0-9]/', '', $ruc);
    if (strlen($ruc) === 15) {
        return substr($ruc, 0, 8) . '-' . substr($ruc, 8, 1) . '-' . substr($ruc, 9);
    }
    return $ruc;
}

function formatearCedula($cedula)
{
    $cedula = preg_replace('/[^0-9]/', '', $cedula);
    if (strlen($cedula) === 7) {
        return substr($cedula, 0, 1) . '-' . substr($cedula, 1, 3) . '-' . substr($cedula, 4);
    }
    return $cedula;
}

function validarArchivo($archivo, $tipos_permitidos = ALLOWED_IMAGE_TYPES)
{
    if (!isset($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) return false;
    if ($archivo['size'] > MAX_FILE_SIZE) return false;
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $tipos_permitidos)) return false;
    return true;
}

function generarNombreArchivo($extension)
{
    return uniqid() . '_' . time() . '.' . $extension;
}

function verificarStockBajo($empresa_id)
{
    $sql = "
    SELECT COUNT(*) as total
    FROM productos 
    WHERE empresa_id = ? 
        AND activo = 1 
        AND stock_actual <= stock_minimo
    ";
    $stmt = ejecutarConsulta($sql, [$empresa_id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $resultado['total'] > 0;
}

function verificarFacturasVencidas($empresa_id)
{
    $sql = "
    SELECT COUNT(*) as total
    FROM facturas_venta 
    WHERE empresa_id = ? 
        AND estado = 'pendiente' 
        AND fecha_vencimiento < CURDATE()
    ";
    $stmt = ejecutarConsulta($sql, [$empresa_id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $resultado['total'] > 0;
}

function verificarComprasPendientes($empresa_id)
{
    $sql = "
    SELECT COUNT(*) as total
    FROM compras 
    WHERE empresa_id = ? 
        AND estado = 'pendiente'
    ";
    $stmt = ejecutarConsulta($sql, [$empresa_id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $resultado['total'] > 0;
}

function obtenerEstadisticasDashboard($empresa_id)
{
    $cache_key = "dashboard_stats_{$empresa_id}";
    $cache_data = obtenerCache($cache_key);
    if ($cache_data !== false) return $cache_data;
    $sql = "
    SELECT 
        (SELECT COUNT(*) FROM facturas_venta 
         WHERE empresa_id = ? AND DATE(fecha_factura) = CURDATE() AND estado != 'anulada') as ventas_hoy,
        (SELECT COALESCE(SUM(total), 0) FROM facturas_venta 
         WHERE empresa_id = ? AND DATE(fecha_factura) = CURDATE() AND estado != 'anulada') as monto_ventas_hoy,
        (SELECT COUNT(*) FROM productos 
         WHERE empresa_id = ? AND activo = 1 AND stock_actual <= stock_minimo) as productos_stock_bajo,
        (SELECT COUNT(*) FROM facturas_venta 
         WHERE empresa_id = ? AND estado = 'pendiente' AND fecha_vencimiento < CURDATE()) as facturas_vencidas,
        (SELECT COUNT(*) FROM compras 
         WHERE empresa_id = ? AND estado = 'pendiente') as compras_pendientes
    ";
    $stmt = ejecutarConsulta($sql, [$empresa_id, $empresa_id, $empresa_id, $empresa_id, $empresa_id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    guardarCache($cache_key, $resultado);
    return $resultado;
}

// ============================================================================
// LOGS Y NOTIFICACIONES
// ============================================================================

function registrarLog($usuario_id, $accion, $detalles = '', $tipo = 'info')
{
    $sql = "
    INSERT INTO logs_actividad (
        usuario_id, empresa_id, accion, detalles, tipo, fecha_registro
    ) VALUES (?, ?, ?, ?, ?, NOW())
    ";
    $empresa_id = 1; // Cambiar por empresa de sesión
    try {
        ejecutarConsulta($sql, [$usuario_id, $empresa_id, $accion, $detalles, $tipo]);
        return true;
    } catch (Exception $e) {
        error_log("Error al registrar log: " . $e->getMessage());
        return false;
    }
}

function registrarError($error, $archivo = '', $linea = 0)
{
    $sql = "
    INSERT INTO logs_errores (
        error, archivo, linea, fecha_registro
    ) VALUES (?, ?, ?, NOW())
    ";
    try {
        ejecutarConsulta($sql, [$error, $archivo, $linea]);
        return true;
    } catch (Exception $e) {
        error_log("Error al registrar error: " . $e->getMessage());
        return false;
    }
}

function enviarNotificacion($usuario_id, $titulo, $mensaje, $tipo = 'info')
{
    $sql = "
    INSERT INTO notificaciones (
        usuario_id, empresa_id, titulo, mensaje, tipo, leida, fecha_registro
    ) VALUES (?, ?, ?, ?, ?, 0, NOW())
    ";
    $empresa_id = 1; // Cambiar por empresa de sesión
    try {
        ejecutarConsulta($sql, [$usuario_id, $empresa_id, $titulo, $mensaje, $tipo]);
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar notificación: " . $e->getMessage());
        return false;
    }
}

function marcarNotificacionLeida($notificacion_id, $usuario_id)
{
    $sql = "
    UPDATE notificaciones 
    SET leida = 1, fecha_lectura = NOW()
    WHERE id = ? AND usuario_id = ?
    ";
    try {
        ejecutarConsulta($sql, [$notificacion_id, $usuario_id]);
        return true;
    } catch (Exception $e) {
        error_log("Error al marcar notificación como leída: " . $e->getMessage());
        return false;
    }
}

function obtenerNotificacionesNoLeidas($usuario_id)
{
    $sql = "
    SELECT id, titulo, mensaje, tipo, fecha_registro
    FROM notificaciones 
    WHERE usuario_id = ? AND leida = 0
    ORDER BY fecha_registro DESC
    LIMIT 10
    ";
    try {
        $stmt = ejecutarConsulta($sql, [$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error al obtener notificaciones: " . $e->getMessage());
        return [];
    }
}

// ============================================================================
// TÉCNICAS DE OPTIMIZACIÓN DE CONSULTAS SQL
// ============================================================================

function obtenerProductosPaginados($pagina = 1, $por_pagina = 20, $filtros = [])
{
    $offset = ($pagina - 1) * $por_pagina;
    $where_conditions = ["p.activo = 1"];
    $params = [];
    if (!empty($filtros['busqueda'])) {
        $where_conditions[] = "(p.nombre LIKE ? OR p.codigo LIKE ?)";
        $busqueda = "%{$filtros['busqueda']}%";
        $params[] = $busqueda;
        $params[] = $busqueda;
    }
    if (!empty($filtros['categoria_id'])) {
        $where_conditions[] = "p.categoria_id = ?";
        $params[] = $filtros['categoria_id'];
    }
    $where_clause = implode(" AND ", $where_conditions);
    $sql = "
    SELECT 
        p.id, p.codigo, p.nombre, p.precio_venta,
        c.nombre as categoria,
        i.stock_actual,
        um.abreviatura as unidad
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN inventario i ON p.id = i.producto_id
    LEFT JOIN unidades_medida um ON p.unidad_medida_id = um.id
    WHERE $where_clause
    ORDER BY p.nombre
    LIMIT $por_pagina OFFSET $offset";
    return ejecutarConsulta($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
}

function buscarPorCodigoBarras($codigo_barras)
{
    $sql = "
    SELECT 
        p.id, p.codigo, p.nombre, p.precio_venta,
        i.stock_actual, um.abreviatura as unidad
    FROM productos p
    LEFT JOIN inventario i ON p.id = i.producto_id
    LEFT JOIN unidades_medida um ON p.unidad_medida_id = um.id
    WHERE p.codigo_barras = ? AND p.activo = 1
    LIMIT 1";
    return ejecutarConsulta($sql, [$codigo_barras])->fetch(PDO::FETCH_ASSOC);
}

function buscarPorCodigo($codigo, $empresa_id = 1)
{
    $sql = "
    SELECT 
        p.id, p.codigo, p.nombre, p.precio_venta,
        i.stock_actual, um.abreviatura as unidad
    FROM productos p
    LEFT JOIN inventario i ON p.id = i.producto_id
    LEFT JOIN unidades_medida um ON p.unidad_medida_id = um.id
    WHERE p.codigo = ? AND p.empresa_id = ? AND p.activo = 1
    LIMIT 1";
    return ejecutarConsulta($sql, [$codigo, $empresa_id])->fetch(PDO::FETCH_ASSOC);
}

function obtenerCategoriasCache()
{
    static $categorias_cache = null;
    if ($categorias_cache === null) {
        $sql = "SELECT id, nombre FROM categorias WHERE empresa_id = 1 AND activo = 1 ORDER BY nombre";
        $categorias_cache = ejecutarConsulta($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    return $categorias_cache;
}

function obtenerEstadisticasCache($tiempo_cache = 300)
{
    static $stats_cache = null;
    static $ultima_actualizacion = 0;
    $tiempo_actual = time();
    if ($stats_cache === null || ($tiempo_actual - $ultima_actualizacion) > $tiempo_cache) {
        $sql = "
        SELECT 
            COUNT(*) as total_productos,
            SUM(CASE WHEN i.stock_actual > p.stock_minimo THEN 1 ELSE 0 END) as stock_normal,
            SUM(CASE WHEN i.stock_actual <= p.stock_minimo AND i.stock_actual > 0 THEN 1 ELSE 0 END) as stock_bajo,
            SUM(CASE WHEN i.stock_actual = 0 THEN 1 ELSE 0 END) as stock_critico
        FROM productos p
        LEFT JOIN inventario i ON p.id = i.producto_id
        WHERE p.activo = 1";
        $stats_cache = ejecutarConsulta($sql)->fetch(PDO::FETCH_ASSOC);
        $ultima_actualizacion = $tiempo_actual;
    }
    return $stats_cache;
}

function buscarProductosLimitado($termino, $limite = 50)
{
    $sql = "
    SELECT 
        p.id, p.codigo, p.nombre, p.precio_venta,
        c.nombre as categoria,
        i.stock_actual,
        um.abreviatura as unidad
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN inventario i ON p.id = i.producto_id
    LEFT JOIN unidades_medida um ON p.unidad_medida_id = um.id
    WHERE p.activo = 1 
    AND (p.nombre LIKE ? OR p.codigo LIKE ?)
    ORDER BY p.nombre
    LIMIT $limite";
    $termino_busqueda = "%$termino%";
    return ejecutarConsulta($sql, [$termino_busqueda, $termino_busqueda])->fetchAll(PDO::FETCH_ASSOC);
}

function ejecutarConsultaConTimeout($sql, $params = [], $timeout = 10)
{
    $conn = getDBConnection();
    $conn->setAttribute(PDO::ATTR_TIMEOUT, $timeout);
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'timeout') !== false) {
            throw new Exception("La consulta tardó demasiado en ejecutarse");
        }
        throw $e;
    }
}

function analizarConsulta($sql, $params = [])
{
    $conn = getDBConnection();
    $explain_sql = "EXPLAIN " . $sql;
    try {
        $stmt = $conn->prepare($explain_sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ["error" => $e->getMessage()];
    }
}

class ConsultasPreparadas
{
    private $conn;
    private $statements = [];
    public function __construct()
    {
        $this->conn = getDBConnection();
    }
    public function obtenerProductosPorCategoria($categoria_id, $limite = 20)
    {
        $key = "productos_categoria_$categoria_id";
        if (!isset($this->statements[$key])) {
            $sql = "
            SELECT p.id, p.codigo, p.nombre, p.precio_venta, i.stock_actual
            FROM productos p
            LEFT JOIN inventario i ON p.id = i.producto_id
            WHERE p.categoria_id = ? AND p.activo = 1
            ORDER BY p.nombre
            LIMIT ?";
            $this->statements[$key] = $this->conn->prepare($sql);
        }
        $this->statements[$key]->execute([$categoria_id, $limite]);
        return $this->statements[$key]->fetchAll(PDO::FETCH_ASSOC);
    }
    public function buscarProductosPorNombre($nombre, $limite = 20)
    {
        $key = "buscar_nombre";
        if (!isset($this->statements[$key])) {
            $sql = "
            SELECT p.id, p.codigo, p.nombre, p.precio_venta, i.stock_actual
            FROM productos p
            LEFT JOIN inventario i ON p.id = i.producto_id
            WHERE p.nombre LIKE ? AND p.activo = 1
            ORDER BY p.nombre
            LIMIT ?";
            $this->statements[$key] = $this->conn->prepare($sql);
        }
        $this->statements[$key]->execute(["%$nombre%", $limite]);
        return $this->statements[$key]->fetchAll(PDO::FETCH_ASSOC);
    }
}

function obtenerProductosEnLotes($tamano_lote = 100)
{
    $offset = 0;
    $productos = [];
    do {
        $sql = "
        SELECT p.id, p.codigo, p.nombre, p.precio_venta
        FROM productos p
        WHERE p.activo = 1
        ORDER BY p.id
        LIMIT $tamano_lote OFFSET $offset";
        $lote = ejecutarConsulta($sql)->fetchAll(PDO::FETCH_ASSOC);
        $productos = array_merge($productos, $lote);
        $offset += $tamano_lote;
        unset($lote);
    } while (count($productos) % $tamano_lote == 0 && count($productos) > 0);
    return $productos;
}

function medirTiempoConsulta($callback, $nombre_consulta = '')
{
    $inicio = microtime(true);
    try {
        $resultado = $callback();
        $fin = microtime(true);
        $tiempo = round(($fin - $inicio) * 1000, 2);
        error_log("Consulta '$nombre_consulta' ejecutada en {$tiempo}ms");
        return [
            'resultado' => $resultado,
            'tiempo_ms' => $tiempo,
            'exito' => true
        ];
    } catch (Exception $e) {
        $fin = microtime(true);
        $tiempo = round(($fin - $inicio) * 1000, 2);
        error_log("Error en consulta '$nombre_consulta' después de {$tiempo}ms: " . $e->getMessage());
        return [
            'error' => $e->getMessage(),
            'tiempo_ms' => $tiempo,
            'exito' => false
        ];
    }
}
