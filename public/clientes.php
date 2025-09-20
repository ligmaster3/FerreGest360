<?php
// ============================================================================
// LÓGICA DE ACCIONES (GUARDAR/ELIMINAR CLIENTE)
// ============================================================================
require_once '../config/connection.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para sanitizar entradas
function sanitizeInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Acción de AGREGAR cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'agregar_cliente') {
    $conn = getDBConnection();
    try {
        // Sanitizar y validar datos del formulario del modal
        $nombre = sanitizeInput($_POST['nombre']);
        $cedula_ruc = sanitizeInput($_POST['cedula_ruc']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null;
        $telefono = sanitizeInput($_POST['telefono']);
        $direccion = sanitizeInput($_POST['direccion']);
        $tipo_cliente = sanitizeInput($_POST['tipo_cliente']);
        $limite_credito = filter_var($_POST['limite_credito'], FILTER_VALIDATE_FLOAT, ['options' => ['default' => 0.0]]);
        $empresa_id = 1; // Asumiendo empresa_id=1
        $activo = 1; // Cliente activo por defecto

        if (empty($nombre) || empty($cedula_ruc)) {
            throw new Exception("Nombre y Cédula/RUC son campos obligatorios.");
        }

        $conn->beginTransaction();

        $sql = "INSERT INTO clientes (nombre, cedula_ruc, email, telefono, direccion, tipo_cliente, limite_credito, empresa_id, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nombre, $cedula_ruc, $email, $telefono, $direccion, $tipo_cliente, $limite_credito, $empresa_id, $activo]);

        $conn->commit();
        $_SESSION['success_message'] = "Cliente '" . htmlspecialchars($nombre) . "' agregado correctamente.";
    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Error: La cédula o RUC '" . htmlspecialchars($cedula_ruc) . "' ya existe.";
        } else {
            $_SESSION['error_message'] = "Error de base de datos al guardar el cliente: " . $e->getMessage();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    // Redirigir para limpiar el POST y mostrar el mensaje
    header('Location: clientes.php');
    exit();
}

// Acción de EDITAR cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar_cliente') {
    $conn = getDBConnection();
    try {
        // Sanitizar y validar datos del formulario
        $cliente_id = filter_var($_POST['cliente_id'], FILTER_VALIDATE_INT);
        $nombre = sanitizeInput($_POST['nombre']);
        $cedula_ruc = sanitizeInput($_POST['cedula_ruc']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null;
        $telefono = sanitizeInput($_POST['telefono']);
        $direccion = sanitizeInput($_POST['direccion']);
        $tipo_cliente = sanitizeInput($_POST['tipo_cliente']);
        $razon_social = sanitizeInput($_POST['razon_social'] ?? '');
        $limite_credito = filter_var($_POST['limite_credito'], FILTER_VALIDATE_FLOAT, ['options' => ['default' => 0.0]]);
        $dias_credito = filter_var($_POST['dias_credito'], FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
        $descuento_porcentaje = filter_var($_POST['descuento_porcentaje'], FILTER_VALIDATE_FLOAT, ['options' => ['default' => 0.0]]);
        $activo = isset($_POST['activo']) ? 1 : 0;
        $empresa_id = 1; // Asumiendo empresa_id=1

        if (!$cliente_id || empty($nombre) || empty($cedula_ruc)) {
            throw new Exception("ID de cliente, nombre y Cédula/RUC son campos obligatorios.");
        }

        $conn->beginTransaction();

        $sql = "UPDATE clientes SET 
            nombre = ?, cedula_ruc = ?, email = ?, telefono = ?, direccion = ?, 
            tipo_cliente = ?, razon_social = ?, limite_credito = ?, dias_credito = ?, 
            descuento_porcentaje = ?, activo = ? 
            WHERE id = ? AND empresa_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $nombre,
            $cedula_ruc,
            $email,
            $telefono,
            $direccion,
            $tipo_cliente,
            $razon_social,
            $limite_credito,
            $dias_credito,
            $descuento_porcentaje,
            $activo,
            $cliente_id,
            $empresa_id
        ]);

        if ($stmt->rowCount() > 0) {
            $conn->commit();
            $_SESSION['success_message'] = "Cliente '" . htmlspecialchars($nombre) . "' actualizado correctamente.";
        } else {
            throw new Exception("No se pudo actualizar el cliente. Verifique que el cliente existe.");
        }
    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error_message'] = "Error: La cédula o RUC '" . htmlspecialchars($cedula_ruc) . "' ya existe para otro cliente.";
        } else {
            $_SESSION['error_message'] = "Error de base de datos al actualizar el cliente: " . $e->getMessage();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    // Redirigir para limpiar el POST y mostrar el mensaje
    header('Location: clientes.php');
    exit();
}


// Acción de ELIMINAR/DESACTIVAR cliente
if (isset($_GET['action']) && $_GET['action'] === 'eliminar' && isset($_GET['id'])) {
    $conn = getDBConnection();
    $id_cliente = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($id_cliente) {
        try {
            // Desactivación lógica para mantener la integridad de los datos
            $sql = "UPDATE clientes SET activo = 0 WHERE id = ? AND empresa_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id_cliente, 1]); // Asumiendo empresa_id = 1

            if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Cliente desactivado correctamente.";
            } else {
                $_SESSION['error_message'] = "No se encontró el cliente o ya estaba inactivo.";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error de base de datos al desactivar el cliente: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "ID de cliente inválido.";
    }

    header('Location: clientes.php');
    exit();
}


// ============================================================================
// FUNCIONES DE CONSULTAS DE CLIENTES
// ============================================================================
function obtenerEstadisticasClientes($empresa_id = 1)
{
    $sql = "
    SELECT 
        COUNT(*) as total_clientes,
        SUM(CASE WHEN tipo_cliente = 'juridico' THEN 1 ELSE 0 END) as clientes_juridicos,
        SUM(CASE WHEN tipo_cliente = 'natural' THEN 1 ELSE 0 END) as clientes_naturales,
        SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as clientes_activos,
        SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as clientes_inactivos,
        COALESCE(SUM(limite_credito), 0) as total_limite_credito,
        COALESCE(AVG(limite_credito), 0) as promedio_limite_credito,
        COALESCE(SUM(dias_credito), 0) as total_dias_credito,
        COALESCE(AVG(dias_credito), 0) as promedio_dias_credito
    FROM clientes 
    WHERE empresa_id = ?";

    try {
        $stmt = ejecutarConsulta($sql, [$empresa_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return ['total_clientes' => 0, 'clientes_juridicos' => 0, 'clientes_naturales' => 0, 'clientes_activos' => 0, 'clientes_inactivos' => 0, 'total_limite_credito' => 0, 'promedio_limite_credito' => 0, 'total_dias_credito' => 0, 'promedio_dias_credito' => 0];
    }
}

function obtenerDeudaTotalClientes($empresa_id = 1)
{
    $sql = "
    SELECT 
        COALESCE(SUM(fv.total), 0) as deuda_total,
        COUNT(DISTINCT fv.cliente_id) as clientes_con_deuda,
        COALESCE(AVG(fv.total), 0) as promedio_deuda
    FROM facturas_venta fv
    WHERE fv.empresa_id = ? 
    AND fv.estado IN ('pendiente', 'vencida')";

    try {
        $stmt = ejecutarConsulta($sql, [$empresa_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return ['deuda_total' => 0, 'clientes_con_deuda' => 0, 'promedio_deuda' => 0];
    }
}

function obtenerClientesPaginados($empresa_id = 1, $pagina = 1, $por_pagina = 20, $filtros = [])
{
    $offset = ($pagina - 1) * $por_pagina;
    $where_conditions = ["c.empresa_id = ?"];
    $params = [$empresa_id];

    if (!empty($filtros['busqueda'])) {
        $where_conditions[] = "(c.nombre LIKE ? OR c.cedula_ruc LIKE ? OR c.telefono LIKE ? OR c.email LIKE ?)";
        $busqueda = "%{$filtros['busqueda']}%";
        $params = array_merge($params, [$busqueda, $busqueda, $busqueda, $busqueda]);
    }

    if (!empty($filtros['tipo_cliente'])) {
        $where_conditions[] = "c.tipo_cliente = ?";
        $params[] = $filtros['tipo_cliente'];
    }

    if (isset($filtros['activo']) && $filtros['activo'] !== '') {
        $where_conditions[] = "c.activo = ?";
        $params[] = $filtros['activo'];
    }

    $where_clause = implode(" AND ", $where_conditions);

    $sql = "
    SELECT 
        c.id, c.codigo, c.tipo_cliente, c.cedula_ruc, c.nombre, c.razon_social, c.direccion,
        c.telefono, c.email, c.limite_credito, c.dias_credito, c.descuento_porcentaje, c.activo, c.fecha_registro,
        COALESCE(COUNT(fv.id), 0) as total_facturas,
        COALESCE(SUM(CASE WHEN fv.estado = 'pagada' THEN fv.total ELSE 0 END), 0) as total_pagado,
        COALESCE(SUM(CASE WHEN fv.estado IN ('pendiente', 'vencida') THEN fv.total ELSE 0 END), 0) as deuda_actual,
        COALESCE(MAX(fv.fecha_factura), 'N/A') as ultima_compra,
        CASE WHEN c.tipo_cliente = 'juridico' THEN 'Profesional' ELSE 'Particular' END as tipo_cliente_desc,
        CASE WHEN c.activo = 1 THEN 'Activo' ELSE 'Inactivo' END as estado_desc,
        CASE WHEN c.limite_credito > 0 THEN ROUND((COALESCE(SUM(CASE WHEN fv.estado IN ('pendiente', 'vencida') THEN fv.total ELSE 0 END), 0) / c.limite_credito) * 100, 1) ELSE 0 END as porcentaje_credito_usado,
        CASE WHEN c.limite_credito > 0 AND COALESCE(SUM(CASE WHEN fv.estado IN ('pendiente', 'vencida') THEN fv.total ELSE 0 END), 0) > 0 THEN 'danger' WHEN c.limite_credito > 0 AND COALESCE(SUM(CASE WHEN fv.estado IN ('pendiente', 'vencida') THEN fv.total ELSE 0 END), 0) = 0 THEN 'success' ELSE 'secondary' END as estado_credito_clase
    FROM clientes c
    LEFT JOIN facturas_venta fv ON c.id = fv.cliente_id
    WHERE $where_clause
    GROUP BY c.id
    ORDER BY c.nombre ASC
    LIMIT $por_pagina OFFSET $offset";

    try {
        $stmt = ejecutarConsulta($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function contarClientes($empresa_id = 1, $filtros = [])
{
    $where_conditions = ["empresa_id = ?"];
    $params = [$empresa_id];

    if (!empty($filtros['busqueda'])) {
        $where_conditions[] = "(nombre LIKE ? OR cedula_ruc LIKE ? OR telefono LIKE ? OR email LIKE ?)";
        $busqueda = "%{$filtros['busqueda']}%";
        $params = array_merge($params, [$busqueda, $busqueda, $busqueda, $busqueda]);
    }

    if (!empty($filtros['tipo_cliente'])) {
        $where_conditions[] = "tipo_cliente = ?";
        $params[] = $filtros['tipo_cliente'];
    }

    if (isset($filtros['activo']) && $filtros['activo'] !== '') {
        $where_conditions[] = "activo = ?";
        $params[] = $filtros['activo'];
    }

    $where_clause = implode(" AND ", $where_conditions);
    $sql = "SELECT COUNT(*) as total FROM clientes WHERE $where_clause";

    try {
        $stmt = ejecutarConsulta($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// ============================================================================
// MANEJO DE ACCIONES AJAX
// ============================================================================

// Endpoint para obtener historial completo del cliente
if (isset($_GET['action']) && $_GET['action'] === 'ver_historial_cliente' && isset($_GET['id'])) {
    header('Content-Type: application/json');

    try {
        $conn = getDBConnection();
        $cliente_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

        if (!$cliente_id) {
            throw new Exception('ID de cliente inválido');
        }

        // 1. Obtener información del cliente
        $sql_cliente = "SELECT id, nombre, tipo_cliente, cedula_ruc, direccion, telefono, email, 
                               limite_credito, dias_credito, descuento_porcentaje, activo, fecha_registro
                        FROM clientes 
                        WHERE id = ? AND empresa_id = 1";
        $stmt = $conn->prepare($sql_cliente);
        $stmt->execute([$cliente_id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cliente) {
            throw new Exception('Cliente no encontrado');
        }

        // 2. Obtener estadísticas del cliente
        $sql_stats = "SELECT 
                        COUNT(*) as total_facturas,
                        SUM(CASE WHEN estado = 'pagada' THEN 1 ELSE 0 END) as facturas_pagadas,
                        SUM(CASE WHEN estado IN ('pendiente', 'vencida') THEN 1 ELSE 0 END) as facturas_pendientes,
                        COALESCE(SUM(total), 0) as total_ventas,
                        COALESCE(SUM(CASE WHEN estado = 'pagada' THEN total ELSE 0 END), 0) as total_pagado,
                        COALESCE(SUM(CASE WHEN estado IN ('pendiente', 'vencida') THEN total ELSE 0 END), 0) as deuda_actual,
                        COALESCE(AVG(total), 0) as promedio_venta,
                        MAX(fecha_factura) as ultima_compra
                      FROM facturas_venta 
                      WHERE cliente_id = ? AND empresa_id = 1";
        $stmt = $conn->prepare($sql_stats);
        $stmt->execute([$cliente_id]);
        $estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calcular porcentaje pagado
        $estadisticas['porcentaje_pagado'] = $estadisticas['total_facturas'] > 0 ?
            round(($estadisticas['facturas_pagadas'] / $estadisticas['total_facturas']) * 100, 1) : 0;

        // 3. Obtener historial de facturas
        $sql_facturas = "SELECT 
                           fv.id,
                           fv.numero_factura,
                           fv.fecha_factura,
                           fv.fecha_vencimiento,
                           fv.total,
                           fv.subtotal,
                           fv.descuento,
                           fv.itbms,
                           fv.estado,
                           fv.tipo_pago,
                           COUNT(dfv.id) as num_productos
                         FROM facturas_venta fv
                         LEFT JOIN detalle_facturas_venta dfv ON fv.id = dfv.factura_id
                         WHERE fv.cliente_id = ? AND fv.empresa_id = 1
                         GROUP BY fv.id
                         ORDER BY fv.fecha_factura DESC
                         LIMIT 50";
        $stmt = $conn->prepare($sql_facturas);
        $stmt->execute([$cliente_id]);
        $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Obtener productos más comprados
        $sql_productos = "SELECT 
                            p.nombre as producto,
                            SUM(dfv.cantidad) as cantidad_total,
                            SUM(dfv.subtotal) as total_gastado,
                            COUNT(DISTINCT fv.id) as num_facturas,
                            AVG(dfv.precio_unitario) as precio_promedio
                          FROM detalle_facturas_venta dfv
                          JOIN facturas_venta fv ON dfv.factura_id = fv.id
                          JOIN productos p ON dfv.producto_id = p.id
                          WHERE fv.cliente_id = ? AND fv.empresa_id = 1
                          GROUP BY p.id, p.nombre
                          ORDER BY cantidad_total DESC
                          LIMIT 10";
        $stmt = $conn->prepare($sql_productos);
        $stmt->execute([$cliente_id]);
        $productos_mas_comprados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Obtener tendencia mensual (últimos 12 meses)
        $sql_tendencia = "SELECT 
                            DATE_FORMAT(fecha_factura, '%Y-%m') as mes,
                            COUNT(*) as num_facturas,
                            COALESCE(SUM(total), 0) as total_ventas,
                            COALESCE(AVG(total), 0) as promedio_venta
                          FROM facturas_venta
                          WHERE cliente_id = ? 
                          AND empresa_id = 1
                          AND fecha_factura >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                          GROUP BY DATE_FORMAT(fecha_factura, '%Y-%m')
                          ORDER BY mes DESC";
        $stmt = $conn->prepare($sql_tendencia);
        $stmt->execute([$cliente_id]);
        $tendencia_mensual = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'cliente' => $cliente,
            'estadisticas' => $estadisticas,
            'facturas' => $facturas,
            'productos_mas_comprados' => $productos_mas_comprados,
            'tendencia_mensual' => $tendencia_mensual
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// ============================================================================
// CARGA DE VISTA
// ============================================================================
include 'partials/head.php';

// Parámetros de paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$clientes_por_pagina = 20; // 🔧 CAMBIAR AQUÍ: Modifica este número para cambiar elementos por página
$offset = ($pagina_actual - 1) * $clientes_por_pagina;

// Parámetros de búsqueda y filtros
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$tipo_cliente = isset($_GET['tipo_cliente']) ? $_GET['tipo_cliente'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Construir filtros
$filtros = [];
if (!empty($busqueda)) $filtros['busqueda'] = $busqueda;
if (!empty($tipo_cliente)) $filtros['tipo_cliente'] = $tipo_cliente;
if ($estado !== '') $filtros['activo'] = $estado;

// Obtener datos
$empresa_id = 1; // 🔧 CAMBIAR AQUÍ: Obtener de sesión
$stats_clientes = obtenerEstadisticasClientes($empresa_id);
$stats_deuda = obtenerDeudaTotalClientes($empresa_id);
$total_clientes = contarClientes($empresa_id, $filtros);
$total_paginas = ceil($total_clientes / $clientes_por_pagina);
$clientes = obtenerClientesPaginados($empresa_id, $pagina_actual, $clientes_por_pagina, $filtros);

// Calcular estadísticas adicionales
$clientes_con_deuda = $stats_deuda['clientes_con_deuda'] ?? 0;
$deuda_total = $stats_deuda['deuda_total'] ?? 0;
include 'partials/head.php';
?>

<body>
    <div id="dashboardScreen" class="dashboard-container">
        <?php include 'partials/sidebar.php'; ?>
        <main class="main-content">
            <?php include 'partials/header.php'; ?>
            <div class="content-area">
                <div class="page-header">
                    <div>
                        <h1>Clientes</h1>
                        <p>Gestión de clientes y créditos</p>
                    </div>
                    <button class="btn-primary" onclick="showAddClientModal()">
                        <i class="fas fa-plus"></i>
                        Nuevo Cliente
                    </button>
                </div>

                <!-- Filtros -->
                <div class="filters-bar bg-light rounded-lg shadow p-6 mb-6">
                    <form method="GET" class="filter-group">
                        <input type="text" name="busqueda" placeholder="Buscar por nombre, cédula, teléfono..."
                            class="filter-input" style="flex-grow: 1;"
                            value="<?php echo htmlspecialchars($busqueda); ?>">

                        <select name="tipo_cliente" class="filter-select">
                            <option value="">Todos los tipos</option>
                            <option value="juridico" <?php echo $tipo_cliente === 'juridico' ? 'selected' : ''; ?>>
                                Profesional</option>
                            <option value="natural" <?php echo $tipo_cliente === 'natural' ? 'selected' : ''; ?>>
                                Particular</option>
                        </select>

                        <select name="estado" class="filter-select">
                            <option value="">Todos los estados</option>
                            <option value="1" <?php echo $estado === '1' ? 'selected' : ''; ?>>Activos</option>
                            <option value="0" <?php echo $estado === '0' ? 'selected' : ''; ?>>Inactivos</option>
                        </select>

                        <button type="submit" class="btn-secondary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </form>
                </div>

                <!-- Estadísticas -->
                <div class="stats-grid rounded-lg p-4 mb-6">
                    <div class="stat-card clients">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-content">
                            <h3>Total Clientes</h3>
                            <p class="stat-value"><?php echo number_format($stats_clientes['total_clientes']); ?></p>
                        </div>
                    </div>
                    <div class="stat-card revenue">
                        <div class="stat-icon"><i class="fas fa-hard-hat"></i></div>
                        <div class="stat-content">
                            <h3>Profesionales</h3>
                            <p class="stat-value"><?php echo number_format($stats_clientes['clientes_juridicos']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="stat-card stock">
                        <div class="stat-icon"><i class="fas fa-credit-card"></i></div>
                        <div class="stat-content">
                            <h3>Con Deuda</h3>
                            <p class="stat-value"><?php echo number_format($clientes_con_deuda); ?></p>
                        </div>
                    </div>
                    <div class="stat-card products">
                        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="stat-content">
                            <h3>Deuda Total</h3>
                            <p class="stat-value">$<?php echo number_format($deuda_total, 2); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de clientes -->
                <div class="data-table">
                    <?php if (empty($clientes)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h3>No se encontraron clientes</h3>
                            <p>No hay clientes que coincidan con los filtros aplicados.</p>
                            <?php if (!empty($busqueda) || !empty($tipo_cliente) || $estado !== ''): ?>
                                <a href="clientes.php" class="btn-primary">Ver todos los clientes</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Contacto</th>
                                    <th>Tipo</th>
                                    <th>Crédito</th>
                                    <th>Estado</th>
                                    <th>Compras Totales</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td>
                                            <div class="client-cell">
                                                <?php
                                                $nombre_cliente = $cliente['nombre'];
                                                $iniciales = '';
                                                $palabras = explode(' ', trim($nombre_cliente));
                                                if (count($palabras) >= 2) {
                                                    $iniciales = strtoupper(substr($palabras[0], 0, 1) . substr(end($palabras), 0, 1));
                                                } elseif (!empty($palabras[0])) {
                                                    $iniciales = strtoupper(substr($palabras[0], 0, 1));
                                                } else {
                                                    $iniciales = '?';
                                                }

                                                // Simple hash para asignar un color consistente
                                                $hash = crc32($cliente['id']);
                                                $colors = ['#e67e22', '#3498db', '#2ecc71', '#9b59b6', '#f1c40f', '#1abc9c', '#e74c3c'];
                                                $avatar_color = $colors[abs($hash) % count($colors)];
                                                ?>
                                                <div class="client-avatar"
                                                    style="background-color: <?php echo $avatar_color; ?>;">
                                                    <?php echo $iniciales; ?>
                                                </div>
                                                <div class="product-info">
                                                    <div class="product-name">
                                                        <?php echo htmlspecialchars($nombre_cliente); ?>
                                                        <?php if (!$cliente['activo']): ?>
                                                            <span class="status-badge inactive">Inactivo</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="product-desc">
                                                        C.I/RUC:
                                                        <?php echo htmlspecialchars($cliente['cedula_ruc'] ?: 'N/A'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="product-info">
                                                <div class="product-name">
                                                    <?php echo htmlspecialchars($cliente['telefono'] ?: 'Sin teléfono'); ?>
                                                </div>
                                                <div class="product-desc">
                                                    <?php echo htmlspecialchars($cliente['email'] ?: 'Sin email'); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="category-badge">
                                                <?php echo $cliente['tipo_cliente_desc']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="credit-info">
                                                <div class="credit-text">
                                                    <span class="font-bold"
                                                        style="color: var(--<?php echo $cliente['estado_credito_clase']; ?>-color);">$<?php echo number_format($cliente['deuda_actual'], 2); ?></span>
                                                    <span class="text-secondary"> /
                                                        $<?php echo number_format($cliente['limite_credito'], 2); ?></span>
                                                </div>
                                                <div class="progress-container">
                                                    <div class="progress-bar"
                                                        style="width: <?php echo htmlspecialchars($cliente['porcentaje_credito_usado']); ?>%; background-color: var(--<?php echo $cliente['estado_credito_clase']; ?>-color);">
                                                    </div>
                                                </div>
                                                <div class="credit-percentage text-secondary">
                                                    <?php echo htmlspecialchars($cliente['porcentaje_credito_usado']); ?>% del
                                                    crédito usado
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($cliente['activo']) : ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="product-info">
                                                <div class="product-name">
                                                    $<?php echo number_format($cliente['total_pagado'] + $cliente['deuda_actual'], 2); ?>
                                                </div>
                                                <div class="product-desc">
                                                    <?php if ($cliente['ultima_compra'] !== 'N/A'): ?>
                                                        Última: <?php echo date('Y-m-d', strtotime($cliente['ultima_compra'])); ?>
                                                    <?php else: ?>
                                                        Sin compras
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-edit" onclick="editarCliente(<?php echo $cliente['id']; ?>)"
                                                    title="Editar Cliente">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                                <button class="btn-history"
                                                    onclick="verHistorialCliente(<?php echo $cliente['id']; ?>)"
                                                    title="Ver Historial de Compras">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                                <button class="btn-delete"
                                                    onclick="eliminarCliente(<?php echo $cliente['id']; ?>, <?php echo json_encode($cliente['nombre']); ?>)"
                                                    title="Desactivar Cliente">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Paginación -->
                        <?php if ($total_paginas > 1): ?>
                            <div class="pagination-container">
                                <div class="pagination-info">
                                    Mostrando <?php echo ($offset + 1); ?> -
                                    <?php echo min($offset + $clientes_por_pagina, $total_clientes); ?>
                                    de <?php echo number_format($total_clientes); ?> clientes
                                </div>
                                <div class="pagination">
                                    <?php if ($pagina_actual > 1): ?>
                                        <a href="?pagina=1<?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?><?php echo !empty($tipo_cliente) ? '&tipo_cliente=' . urlencode($tipo_cliente) : ''; ?><?php echo $estado !== '' ? '&estado=' . urlencode($estado) : ''; ?>"
                                            class="page-link">
                                            <i class="fas fa-angle-double-left"></i>
                                        </a>
                                        <a href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?><?php echo !empty($tipo_cliente) ? '&tipo_cliente=' . urlencode($tipo_cliente) : ''; ?><?php echo $estado !== '' ? '&estado=' . urlencode($estado) : ''; ?>"
                                            class="page-link">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php
                                    $inicio = max(1, $pagina_actual - 2);
                                    $fin = min($total_paginas, $pagina_actual + 2);

                                    for ($i = $inicio; $i <= $fin; $i++):
                                    ?>
                                        <a href="?pagina=<?php echo $i; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?><?php echo !empty($tipo_cliente) ? '&tipo_cliente=' . urlencode($tipo_cliente) : ''; ?><?php echo $estado !== '' ? '&estado=' . urlencode($estado) : ''; ?>"
                                            class="page-link <?php echo $i === $pagina_actual ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($pagina_actual < $total_paginas): ?>
                                        <a href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?><?php echo !empty($tipo_cliente) ? '&tipo_cliente=' . urlencode($tipo_cliente) : ''; ?><?php echo $estado !== '' ? '&estado=' . urlencode($estado) : ''; ?>"
                                            class="page-link">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                        <a href="?pagina=<?php echo $total_paginas; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?><?php echo !empty($tipo_cliente) ? '&tipo_cliente=' . urlencode($tipo_cliente) : ''; ?><?php echo $estado !== '' ? '&estado=' . urlencode($estado) : ''; ?>"
                                            class="page-link">
                                            <i class="fas fa-angle-double-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <?php include 'partials/modals.php'; ?>

    <script>
        // Funciones JavaScript para acciones de clientes

        // Función para mostrar modal de agregar cliente
        function showAddClientModal() {
            document.getElementById('addClientModal').classList.remove('hidden');
        }

        // Función para editar cliente
        async function editarCliente(clienteId) {
            try {
                const response = await fetch(`api/get_cliente_detalle.php?id=${clienteId}`);
                const data = await response.json();

                if (data.success) {
                    const cliente = data.cliente;

                    // Llenar el formulario de edición
                    document.getElementById('edit_cliente_id').value = cliente.id;
                    document.getElementById('edit_codigo').value = cliente.codigo || '';
                    document.getElementById('edit_tipo_cliente').value = cliente.tipo_cliente;
                    document.getElementById('edit_nombre').value = cliente.nombre || '';
                    document.getElementById('edit_razon_social').value = cliente.razon_social || '';
                    document.getElementById('edit_cedula_ruc').value = cliente.cedula_ruc || '';
                    document.getElementById('edit_telefono').value = cliente.telefono || '';
                    document.getElementById('edit_email').value = cliente.email || '';
                    document.getElementById('edit_direccion').value = cliente.direccion || '';
                    document.getElementById('edit_limite_credito').value = cliente.limite_credito || '0.00';
                    document.getElementById('edit_dias_credito').value = cliente.dias_credito || '0';
                    document.getElementById('edit_descuento_porcentaje').value = cliente.descuento_porcentaje || '0.00';
                    document.getElementById('edit_activo').checked = cliente.activo == 1;

                    // Mostrar/ocultar campos según tipo de cliente
                    toggleEditClienteFields();

                    // Mostrar modal
                    document.getElementById('editClientModal').classList.remove('hidden');
                } else {
                    alert('Error: ' + (data.error || 'No se pudo cargar la información del cliente'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar la información del cliente');
            }
        }

        // Función para ver historial de cliente
        async function verHistorialCliente(clienteId) {
            try {
                // Mostrar modal con spinner
                document.getElementById('clientHistoryModal').classList.remove('hidden');
                document.getElementById('clientHistoryContent').innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-4"></i>
                        <p class="text-gray-600">Cargando historial del cliente...</p>
                    </div>
                `;

                const response = await fetch(`clientes.php?action=ver_historial_cliente&id=${clienteId}`);
                const data = await response.json();

                if (data.success) {
                    mostrarHistorialCliente(data);
                } else {
                    document.getElementById('clientHistoryContent').innerHTML = `
                        <div class="error-message text-center py-8">
                            <i class="fas fa-exclamation-triangle fa-2x text-red-500 mb-4"></i>
                            <p class="text-red-600">${data.error || 'Error al cargar el historial'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('clientHistoryContent').innerHTML = `
                    <div class="error-message text-center py-8">
                        <i class="fas fa-exclamation-triangle fa-2x text-red-500 mb-4"></i>
                        <p class="text-red-600">Error de conexión al cargar el historial del cliente</p>
                    </div>
                `;
            }
        }

        // Función para mostrar el historial de cliente
        function mostrarHistorialCliente(data) {
            const {
                cliente,
                estadisticas,
                facturas,
                productos_mas_comprados,
                tendencia_mensual
            } = data;

            let html = `
                <div class="client-history">
                    <!-- Información del cliente -->
                    <div class="client-info-section">
                        <h3><i class="fas fa-user"></i> ${cliente.nombre}</h3>
                        <p><strong>Tipo:</strong> ${cliente.tipo_cliente === 'juridico' ? 'Profesional' : 'Particular'}</p>
                        <p><strong>Cédula/RUC:</strong> ${cliente.cedula_ruc}</p>
                        ${cliente.telefono ? `<p><strong>Teléfono:</strong> ${cliente.telefono}</p>` : ''}
                        ${cliente.email ? `<p><strong>Email:</strong> ${cliente.email}</p>` : ''}
                        ${cliente.direccion ? `<p><strong>Dirección:</strong> ${cliente.direccion}</p>` : ''}
                        ${cliente.limite_credito > 0 ? `<p><strong>Límite de Crédito:</strong> $${parseFloat(cliente.limite_credito).toFixed(2)}</p>` : ''}
                        ${cliente.dias_credito > 0 ? `<p><strong>Días de Crédito:</strong> ${cliente.dias_credito} días</p>` : ''}
                        ${estadisticas.ultima_compra && estadisticas.ultima_compra !== 'N/A' ? `<p><strong>Última Compra:</strong> ${new Date(estadisticas.ultima_compra).toLocaleDateString()}</p>` : ''}
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                            <div class="stat-content">
                                <h4>Total Facturas</h4>
                                <p class="stat-value">${estadisticas.total_facturas}</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                            <div class="stat-content">
                                <h4>Total Ventas</h4>
                                <p class="stat-value">$${parseFloat(estadisticas.total_ventas).toFixed(2)}</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-content">
                                <h4>Facturas Pagadas</h4>
                                <p class="stat-value">${estadisticas.facturas_pagadas} (${estadisticas.porcentaje_pagado}%)</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="stat-content">
                                <h4>Deuda Actual</h4>
                                <p class="stat-value">$${parseFloat(estadisticas.deuda_actual).toFixed(2)}</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
                            <div class="stat-content">
                                <h4>Promedio por Factura</h4>
                                <p class="stat-value">$${parseFloat(estadisticas.promedio_venta).toFixed(2)}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Historial de facturas -->
                    <div class="history-section">
                        <h4><i class="fas fa-list"></i> Historial de Facturas</h4>
                        ${facturas.length > 0 ? `
                            <div class="table-responsive">
                                <table class="history-table">
                                    <thead>
                                        <tr>
                                            <th>Factura</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Productos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${facturas.map(factura => `
                                            <tr>
                                                <td>
                                                    <a href="#" onclick="verFactura(${factura.id})" class="text-primary font-weight-bold">
                                                        ${factura.numero_factura}
                                                    </a>
                                                </td>
                                                <td>${new Date(factura.fecha_factura).toLocaleDateString()}</td>
                                                <td>$${parseFloat(factura.total).toFixed(2)}</td>
                                                <td>
                                                    <span class="badge ${getEstadoClass(factura.estado)}">
                                                        ${getEstadoText(factura.estado)}
                                                    </span>
                                                </td>
                                                <td>${factura.num_productos}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        ` : '<p class="no-data">No hay facturas registradas</p>'}
                    </div>
                    
                    <!-- Productos más comprados -->
                    ${productos_mas_comprados.length > 0 ? `
                        <div class="history-section">
                            <h4><i class="fas fa-shopping-cart"></i> Productos Más Comprados</h4>
                            <div class="table-responsive">
                                <table class="history-table">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Total Gastado</th>
                                            <th>Facturas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${productos_mas_comprados.map(producto => `
                                            <tr>
                                                <td>${producto.producto}</td>
                                                <td>${producto.cantidad_total}</td>
                                                <td>$${parseFloat(producto.total_gastado).toFixed(2)}</td>
                                                <td>${producto.num_facturas}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Tendencia mensual -->
                    ${tendencia_mensual.length > 0 ? `
                        <div class="history-section">
                            <h4><i class="fas fa-chart-line"></i> Tendencia Mensual (Últimos 12 Meses)</h4>
                            <div class="table-responsive">
                                <table class="history-table">
                                    <thead>
                                        <tr>
                                            <th>Mes</th>
                                            <th>Facturas</th>
                                            <th>Total Ventas</th>
                                            <th>Promedio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${tendencia_mensual.map(mes => `
                                            <tr>
                                                <td>${new Date(mes.mes + '-01').toLocaleDateString('es-ES', {year: 'numeric', month: 'long'})}</td>
                                                <td>${mes.num_facturas}</td>
                                                <td>$${parseFloat(mes.total_ventas).toFixed(2)}</td>
                                                <td>$${parseFloat(mes.promedio_venta).toFixed(2)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;

            document.getElementById('clientHistoryContent').innerHTML = html;
        }

        // Función para eliminar/desactivar cliente
        function eliminarCliente(clienteId, nombreCliente) {
            if (confirm(
                    `¿Estás seguro de que quieres desactivar al cliente "${nombreCliente}"? El cliente no podrá ser usado para nuevas transacciones pero su historial se conservará.`
                )) {
                window.location.href = `clientes.php?action=eliminar&id=${clienteId}`;
            }
        }

        // Funciones auxiliares
        function toggleClienteFields() {
            const tipoCliente = document.getElementById('tipo_cliente').value;
            const nombreGrupo = document.getElementById('nombre_grupo');
            const razonSocialGrupo = document.getElementById('razon_social_grupo');
            const nombreInput = document.getElementById('nombre');
            const razonSocialInput = document.getElementById('razon_social');

            if (tipoCliente === 'juridico') {
                nombreGrupo.style.display = 'none';
                razonSocialGrupo.style.display = 'block';
                nombreInput.removeAttribute('required');
                razonSocialInput.setAttribute('required', 'required');
            } else {
                nombreGrupo.style.display = 'block';
                razonSocialGrupo.style.display = 'none';
                nombreInput.setAttribute('required', 'required');
                razonSocialInput.removeAttribute('required');
            }
        }

        function toggleEditClienteFields() {
            const tipoCliente = document.getElementById('edit_tipo_cliente').value;
            const nombreGrupo = document.getElementById('edit_nombre_grupo');
            const razonSocialGrupo = document.getElementById('edit_razon_social_grupo');
            const nombreInput = document.getElementById('edit_nombre');
            const razonSocialInput = document.getElementById('edit_razon_social');

            if (tipoCliente === 'juridico') {
                nombreGrupo.style.display = 'none';
                razonSocialGrupo.style.display = 'block';
                nombreInput.removeAttribute('required');
                razonSocialInput.setAttribute('required', 'required');
            } else {
                nombreGrupo.style.display = 'block';
                razonSocialGrupo.style.display = 'none';
                nombreInput.setAttribute('required', 'required');
                razonSocialInput.removeAttribute('required');
            }
        }

        function getEstadoClass(estado) {
            switch (estado) {
                case 'pagada':
                    return 'bg-success';
                case 'pendiente':
                    return 'bg-warning';
                case 'vencida':
                    return 'bg-danger';
                case 'anulada':
                    return 'bg-secondary';
                default:
                    return 'bg-secondary';
            }
        }

        function getEstadoText(estado) {
            switch (estado) {
                case 'pagada':
                    return 'Pagada';
                case 'pendiente':
                    return 'Pendiente';
                case 'vencida':
                    return 'Vencida';
                case 'anulada':
                    return 'Anulada';
                default:
                    return estado;
            }
        }

        // Función para cerrar el modal del historial
        function closeClientHistoryModal() {
            document.getElementById('clientHistoryModal').classList.add('hidden');
        }

        // Mostrar mensajes de éxito/error si existen
        <?php if (isset($_SESSION['success_message'])): ?>
            alert('<?php echo addslashes($_SESSION['success_message']); ?>');
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            alert('<?php echo addslashes($_SESSION['error_message']); ?>');
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </script>
</body>

</html>