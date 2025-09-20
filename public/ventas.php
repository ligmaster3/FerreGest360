<?php
require_once '../config/connection.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// LÓGICA DE ACCIONES (VER, PAGAR, ANULAR FACTURA)
// ============================================================================

// --- Acción para obtener detalles de una factura (AJAX) ---
if (isset($_GET['action']) && $_GET['action'] === 'get_factura_details' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        echo json_encode(['error' => 'ID de factura inválido']);
        exit;
    }

    // Consulta principal de la factura
    $sql_factura = "SELECT fv.*, c.nombre as cliente_nombre, c.cedula_ruc, c.direccion, u.nombre as vendedor_nombre, u.apellido as vendedor_apellido FROM facturas_venta fv LEFT JOIN clientes c ON fv.cliente_id = c.id LEFT JOIN usuarios u ON fv.vendedor_id = u.id WHERE fv.id = ?";
    $factura = ejecutarConsulta($sql_factura, [$id])->fetch(PDO::FETCH_ASSOC);

    if (!$factura) {
        echo json_encode(['error' => 'Factura no encontrada']);
        exit;
    }

    // Consulta de items de la factura
    $sql_items = "SELECT dfv.*, p.nombre as producto_nombre, p.codigo FROM detalle_facturas_venta dfv JOIN productos p ON dfv.producto_id = p.id WHERE dfv.factura_id = ?";
    $factura['items'] = ejecutarConsulta($sql_items, [$id])->fetchAll(PDO::FETCH_ASSOC);

    // Consulta de pagos
    $sql_pagos = "SELECT * FROM pagos_facturas WHERE factura_id = ?";
    $factura['pagos'] = ejecutarConsulta($sql_pagos, [$id])->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totales desde los items (por si los globales no están correctos)
    $subtotal = 0;
    $descuento = 0;
    foreach ($factura['items'] as $item) {
        $subtotal += $item['subtotal'];
        $descuento += $item['descuento'];
    }
    $itbms = isset($factura['itbms']) ? $factura['itbms'] : 0;
    $total = $subtotal - $descuento + $itbms;

    $factura['subtotal_calc'] = $subtotal;
    $factura['descuento_calc'] = $descuento;
    $factura['itbms_calc'] = $itbms;
    $factura['total_calc'] = $total;

    echo json_encode($factura);
    exit;
}

// --- Acción para registrar un pago (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'registrar_pago') {
    $conn = getDBConnection();
    try {
        $factura_id = filter_var($_POST['factura_id'], FILTER_VALIDATE_INT);
        $monto = filter_var($_POST['monto_pago'], FILTER_VALIDATE_FLOAT);
        $forma_pago = htmlspecialchars(strip_tags($_POST['forma_pago']));
        $fecha_pago = $_POST['fecha_pago'];

        if (!$factura_id || !$monto || empty($forma_pago) || empty($fecha_pago)) {
            throw new Exception("Datos del pago incompletos.");
        }

        $conn->beginTransaction();

        // Insertar pago
        $sql_pago = "INSERT INTO pagos_facturas (factura_id, monto, forma_pago, fecha_pago, empresa_id) VALUES (?, ?, ?, ?, 1)";
        ejecutarConsulta($sql_pago, [$factura_id, $monto, $forma_pago, $fecha_pago]);

        // Actualizar estado de la factura
        $sql_total = "SELECT total FROM facturas_venta WHERE id = ?";
        $factura = ejecutarConsulta($sql_total, [$factura_id])->fetch(PDO::FETCH_ASSOC);
        $total_factura = $factura['total'];

        $sql_pagado = "SELECT SUM(monto) as total_pagado FROM pagos_facturas WHERE factura_id = ?";
        $pago_total = ejecutarConsulta($sql_pagado, [$factura_id])->fetch(PDO::FETCH_ASSOC);
        $total_pagado = $pago_total['total_pagado'];

        if ($total_pagado >= $total_factura) {
            $sql_update = "UPDATE facturas_venta SET estado = 'pagada' WHERE id = ?";
            ejecutarConsulta($sql_update, [$factura_id]);
        }

        $conn->commit();
        $_SESSION['success_message'] = "Pago registrado correctamente.";
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        $_SESSION['error_message'] = "Error al registrar el pago: " . $e->getMessage();
    }

    header('Location: ventas.php');
    exit;
}

// --- Acción para anular una factura (GET) ---
if (isset($_GET['action']) && $_GET['action'] === 'anular' && isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id) {
        try {
            // Aquí se podría añadir la lógica para devolver el stock al inventario
            $sql = "UPDATE facturas_venta SET estado = 'anulada' WHERE id = ? AND estado IN ('pendiente', 'vencida')";
            $stmt = ejecutarConsulta($sql, [$id]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Factura anulada correctamente.";
            } else {
                $_SESSION['error_message'] = "La factura no se pudo anular (ya está pagada o ya fue anulada).";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al anular la factura: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "ID de factura inválido.";
    }
    header('Location: ventas.php');
    exit;
}

// ============================================================================
// CARGA DE VISTA
// ============================================================================
include 'partials/head.php';

// Parámetros de paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$ventas_por_pagina = 20; // 🔧 CAMBIAR AQUÍ: Modifica este número para cambiar elementos por página
$offset = ($pagina_actual - 1) * $ventas_por_pagina;

// Parámetros de búsqueda y filtros
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$cliente_id = isset($_GET['cliente']) ? (int)$_GET['cliente'] : 0;
$estado_factura = isset($_GET['estado']) ? $_GET['estado'] : '';
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Construir la consulta base con condiciones WHERE
$where_conditions = ["fv.empresa_id = 1"];
$params = [];

// Agregar condición de búsqueda si existe
if (!empty($busqueda)) {
    $where_conditions[] = "(fv.numero_factura LIKE ? OR c.nombre LIKE ? OR c.cedula_ruc LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
}

// Agregar filtro por cliente
if ($cliente_id > 0) {
    $where_conditions[] = "fv.cliente_id = ?";
    $params[] = $cliente_id;
}

// Agregar filtro por estado de factura
if (!empty($estado_factura)) {
    $where_conditions[] = "fv.estado = ?";
    $params[] = $estado_factura;
}

// Agregar filtros de fecha
if (!empty($fecha_inicio)) {
    $where_conditions[] = "fv.fecha_factura >= ?";
    $params[] = $fecha_inicio;
}

if (!empty($fecha_fin)) {
    $where_conditions[] = "fv.fecha_factura <= ?";
    $params[] = $fecha_fin;
}

$where_clause = implode(" AND ", $where_conditions);

// Consulta para contar total de registros (para paginación)
$sql_count = "
SELECT COUNT(*) as total
FROM facturas_venta fv
LEFT JOIN clientes c ON fv.cliente_id = c.id
WHERE $where_clause";

try {
    $total_result = ejecutarConsulta($sql_count, $params)->fetch(PDO::FETCH_ASSOC);
    $total_ventas = $total_result['total'] ?? 0;
    $total_paginas = ceil($total_ventas / $ventas_por_pagina);
} catch (Exception $e) {
    $total_ventas = 0;
    $total_paginas = 0;
}

// Consulta para obtener estadísticas de ventas con manejo de NULL
$sql_stats = "
SELECT 
    COUNT(*) as total_facturas,
    SUM(CASE WHEN fv.estado = 'pagada' THEN 1 ELSE 0 END) as facturas_pagadas,
    SUM(CASE WHEN fv.estado = 'pendiente' THEN 1 ELSE 0 END) as facturas_pendientes,
    SUM(CASE WHEN fv.estado = 'vencida' THEN 1 ELSE 0 END) as facturas_vencidas,
    SUM(CASE WHEN fv.estado = 'anulada' THEN 1 ELSE 0 END) as facturas_anuladas,
    COALESCE(SUM(fv.total), 0) as total_ventas,
    COALESCE(AVG(fv.total), 0) as promedio_venta,
    COALESCE(SUM(fv.descuento), 0) as total_descuentos,
    COALESCE(SUM(fv.itbms), 0) as total_itbms
FROM facturas_venta fv
WHERE fv.empresa_id = 1";

try {
    $stats = ejecutarConsulta($sql_stats)->fetch(PDO::FETCH_ASSOC);
    // Asegurar que todos los valores sean números
    $stats = array_map(function ($value) {
        return is_numeric($value) ? $value : 0;
    }, $stats);
} catch (Exception $e) {
    $stats = [
        'total_facturas' => 0,
        'facturas_pagadas' => 0,
        'facturas_pendientes' => 0,
        'facturas_vencidas' => 0,
        'facturas_anuladas' => 0,
        'total_ventas' => 0,
        'promedio_venta' => 0,
        'total_descuentos' => 0,
        'total_itbms' => 0
    ];
}

// Consulta para obtener clientes para el filtro
$sql_clientes = "
SELECT id, nombre, cedula_ruc 
FROM clientes 
WHERE empresa_id = 1 AND activo = 1 
ORDER BY nombre";

try {
    $clientes = ejecutarConsulta($sql_clientes)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $clientes = [];
}

// Consulta principal optimizada con LIMIT y OFFSET
$sql_ventas = "
SELECT 
    fv.id,
    fv.numero_factura,
    fv.fecha_factura,
    fv.fecha_vencimiento,
    fv.tipo_pago,
    COALESCE(fv.subtotal, 0) as subtotal,
    COALESCE(fv.descuento, 0) as descuento,
    COALESCE(fv.itbms, 0) as itbms,
    COALESCE(fv.total, 0) as total,
    fv.estado,
    fv.observaciones,
    fv.fecha_creacion,
    
    -- Información del cliente
    c.id as cliente_id,
    COALESCE(c.nombre, 'Cliente no encontrado') as cliente_nombre,
    COALESCE(c.cedula_ruc, 'Sin cédula') as cliente_cedula,
    COALESCE(c.telefono, 'Sin teléfono') as cliente_telefono,
    
    -- Información del vendedor
    COALESCE(u.nombre, 'Vendedor no encontrado') as vendedor_nombre,
    COALESCE(u.apellido, '') as vendedor_apellido,
    
    -- Cálculos derivados
    CASE 
        WHEN fv.estado = 'pagada' THEN 'success'
        WHEN fv.estado = 'pendiente' THEN 'warning'
        WHEN fv.estado = 'vencida' THEN 'danger'
        WHEN fv.estado = 'anulada' THEN 'secondary'
        ELSE 'secondary'
    END as estado_clase,
    
    CASE 
        WHEN fv.estado = 'pagada' THEN 'Pagada'
        WHEN fv.estado = 'pendiente' THEN 'Pendiente'
        WHEN fv.estado = 'vencida' THEN 'Vencida'
        WHEN fv.estado = 'anulada' THEN 'Anulada'
        ELSE 'Desconocido'
    END as estado_descripcion,
    
    CASE 
        WHEN fv.fecha_vencimiento IS NULL THEN NULL
        ELSE DATEDIFF(fv.fecha_vencimiento, CURDATE())
    END as dias_vencimiento

FROM facturas_venta fv
LEFT JOIN clientes c ON fv.cliente_id = c.id
LEFT JOIN usuarios u ON fv.vendedor_id = u.id
WHERE $where_clause
ORDER BY fv.fecha_factura DESC, fv.id DESC
LIMIT $ventas_por_pagina OFFSET $offset";

try {
    $ventas = ejecutarConsulta($sql_ventas, $params)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $ventas = [];
}

// Verificar si hay datos de ejemplo para mostrar
$hay_datos = !empty($ventas) || $stats['total_facturas'] > 0;
?>

<body>
    <div id="dashboardScreen" class="dashboard-container">
        <?php include 'partials/sidebar.php'; ?>
        <main class="main-content">
            <?php include 'partials/header.php'; ?>
            <div class="content-area">
                <div class="page-header">
                    <h1>Ventas</h1>
                    <p>Gestión de facturas y ventas</p>
                </div>
 <!-- Filtros mejorados -->
                <div class="filters-bar">
                    <form method="GET" action="" class="filter-form">
                        <div class="filter-group">
                            <input type="text" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>"
                                placeholder="Número factura, Cliente, Cédula..." class="filter-input"
                                style="flex-grow: 1;">

                            <select name="cliente" class="filter-select">
                                <option value="">Todos los clientes</option>
                                <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>"
                                    <?php echo $cliente_id == $cliente['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cliente['nombre'] . ' (' . $cliente['cedula_ruc'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>

                            <select name="estado" class="filter-select">
                                <option value="">Todos los estados</option>
                                <option value="pagada" <?php echo $estado_factura == 'pagada' ? 'selected' : ''; ?>>
                                    Pagada</option>
                                <option value="pendiente"
                                    <?php echo $estado_factura == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="vencida" <?php echo $estado_factura == 'vencida' ? 'selected' : ''; ?>>
                                    Vencida</option>
                                <option value="anulada" <?php echo $estado_factura == 'anulada' ? 'selected' : ''; ?>>
                                    Anulada</option>
                            </select>

                            <input type="date" name="fecha_inicio"
                                value="<?php echo htmlspecialchars($fecha_inicio); ?>" class="filter-input"
                                placeholder="Fecha inicio">

                            <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>"
                                class="filter-input" placeholder="Fecha fin">

                            <button type="submit" class="btn-secondary">
                                <i class="fas fa-search"></i> Buscar
                            </button>

                            <?php if (!empty($busqueda) || $cliente_id > 0 || !empty($estado_factura) || !empty($fecha_inicio) || !empty($fecha_fin)): ?>
                            <a href="ventas.php" class="btn-secondary btn-clear">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Mensaje si no hay datos -->
                <?php if (!$hay_datos): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <div class="text-center">
                        <i class="fas fa-info-circle text-blue-500 text-3xl mb-3"></i>
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">No hay datos de ventas</h3>
                        <p class="text-blue-600 mb-4">
                            Para ver información de ventas, necesitas crear facturas primero.
                        </p>
                        <button class="btn-primary" onclick="crearNuevaFactura()">
                            <i class="fas fa-plus"></i> Crear Nueva Factura
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="stats-grid">
                    <div class="invecard">
                        <div class="stat-content">
                            <h3 class="text-primary text-center">Total Facturas</h3>
                            <p class="stat-value text-info text-center">
                                <?php echo number_format($stats['total_facturas']); ?></p>
                        </div>
                    </div>
                    <div class="invecard">
                        <div class="stat-content">
                            <h3 class="text-success text-center">Pagadas</h3>
                            <p class="stat-value text-success text-center">
                                <?php echo number_format($stats['facturas_pagadas']); ?></p>
                        </div>
                    </div>
                    <div class="invecard">
                        <div class="stat-content">
                            <h3 class="text-warning text-center">Pendientes</h3>
                            <p class="stat-value text-warning text-center">
                                <?php echo number_format($stats['facturas_pendientes']); ?></p>
                        </div>
                    </div>
                    <div class="invecard">
                        <div class="stat-content">
                            <h3 class="text-danger text-center">Vencidas</h3>
                            <p class="stat-value text-danger text-center">
                                <?php echo number_format($stats['facturas_vencidas']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Resumen de ventas -->
                <?php if ($hay_datos): ?>
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <h4 class="text-lg font-semibold text-gray-700">Total Ventas</h4>
                            <p class="text-2xl font-bold text-green-600">
                                $<?php echo number_format($stats['total_ventas'], 2); ?></p>
                        </div>
                        <div class="text-center">
                            <h4 class="text-lg font-semibold text-gray-700">Promedio por Factura</h4>
                            <p class="text-2xl font-bold text-blue-600">
                                $<?php echo number_format($stats['promedio_venta'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tabla de ventas -->
                <div class="data-table">
                    <div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Factura</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Vencimiento</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Vendedor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="ventasTableBody">
                                <?php if (empty($ventas)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-8 text-gray-500">
                                        <i class="fas fa-search fa-2x mb-2"></i>
                                        <p>
                                            <?php if ($hay_datos): ?>
                                            No se encontraron facturas con los filtros aplicados
                                            <?php else: ?>
                                            No hay facturas registradas. Crea tu primera factura para comenzar.
                                            <?php endif; ?>
                                        </p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($ventas as $venta): ?>
                                <tr class="venta-row">
                                    <td>
                                        <div class="factura-info">
                                            <div class="factura-numero">
                                                <?php echo htmlspecialchars($venta['numero_factura']); ?></div>
                                            <div class="factura-tipo"><?php echo ucfirst($venta['tipo_pago']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="cliente-info">
                                            <div class="cliente-nombre">
                                                <?php echo htmlspecialchars($venta['cliente_nombre']); ?></div>
                                            <div class="cliente-cedula">
                                                <?php echo htmlspecialchars($venta['cliente_cedula']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fecha-info">
                                            <div class="fecha-factura">
                                                <?php echo date('d/m/Y', strtotime($venta['fecha_factura'])); ?></div>
                                            <div class="fecha-hora">
                                                <?php echo date('H:i', strtotime($venta['fecha_creacion'])); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($venta['fecha_vencimiento']): ?>
                                        <div class="vencimiento-info">
                                            <div class="fecha-vencimiento">
                                                <?php echo date('d/m/Y', strtotime($venta['fecha_vencimiento'])); ?>
                                            </div>
                                            <?php if ($venta['dias_vencimiento'] !== null): ?>
                                            <?php if ($venta['dias_vencimiento'] < 0): ?>
                                            <div class="dias-vencido">Vencida hace
                                                <?php echo abs($venta['dias_vencimiento']); ?> días</div>
                                            <?php elseif ($venta['dias_vencimiento'] <= 7): ?>
                                            <div class="dias-vencimiento">Vence en
                                                <?php echo $venta['dias_vencimiento']; ?> días</div>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-gray-400">Sin vencimiento</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="total-info">
                                            <div class="total-monto">$<?php echo number_format($venta['total'], 2); ?>
                                            </div>
                                            <?php if ($venta['descuento'] > 0): ?>
                                            <div class="descuento-info text-green-600">
                                                -<?php echo number_format($venta['descuento'], 2); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="estado-badge <?php echo $venta['estado_clase']; ?>">
                                            <?php echo $venta['estado_descripcion']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="vendedor-info">
                                            <?php echo htmlspecialchars($venta['vendedor_nombre'] . ' ' . $venta['vendedor_apellido']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <div class="dropdown">
                                                <button class="btn-secondary px-3 py-1 rounded-md dropdown-toggle">
                                                    Acciones <i class="fas fa-chevron-down ml-1"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a href="#" onclick="verFactura(<?php echo $venta['id']; ?>)"
                                                        class="dropdown-item">
                                                        <i class="fas fa-eye mr-2"></i>Ver Detalle
                                                    </a>
                                                    <?php if (in_array($venta['estado'], ['pendiente', 'vencida'])): ?>
                                                    <a href="#"
                                                        onclick="abrirModalPago(<?php echo $venta['id']; ?>, <?php echo $venta['total']; ?>)"
                                                        class="dropdown-item">
                                                        <i class="fas fa-dollar-sign mr-2"></i>Registrar Pago
                                                    </a>
                                                    <a href="#"
                                                        onclick="anularFactura(<?php echo $venta['id']; ?>, '<?php echo htmlspecialchars($venta['numero_factura']); ?>')"
                                                        class="dropdown-item text-danger">
                                                        <i class="fas fa-times-circle mr-2"></i>Anular Factura
                                                    </a>
                                                    <?php endif; ?>
                                                    <a href="#" onclick="imprimirFactura(<?php echo $venta['id']; ?>)"
                                                        class="dropdown-item">
                                                        <i class="fas fa-print mr-2"></i>Imprimir
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <div class="pagination-container">
                    <nav class="pagination-nav">
                        <ul class="pagination-list">
                            <!-- Botón Anterior -->
                            <?php if ($pagina_actual > 1): ?>
                            <li>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])); ?>"
                                    class="pagination-link">
                                    <i class="fas fa-chevron-left"></i> Anterior
                                </a>
                            </li>
                            <?php endif; ?>

                            <!-- Números de página -->
                            <?php
                                $inicio = max(1, $pagina_actual - 2);
                                $fin = min($total_paginas, $pagina_actual + 2);

                                if ($inicio > 1): ?>
                            <li><a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => 1])); ?>"
                                    class="pagination-link">1</a></li>
                            <?php if ($inicio > 2): ?>
                            <li><span class="pagination-ellipsis">...</span></li>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                            <li>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>"
                                    class="pagination-link <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>

                            <?php if ($fin < $total_paginas): ?>
                            <?php if ($fin < $total_paginas - 1): ?>
                            <li><span class="pagination-ellipsis">...</span></li>
                            <?php endif; ?>
                            <li><a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $total_paginas])); ?>"
                                    class="pagination-link"><?php echo $total_paginas; ?></a></li>
                            <?php endif; ?>

                            <!-- Botón Siguiente -->
                            <?php if ($pagina_actual < $total_paginas): ?>
                            <li>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])); ?>"
                                    class="pagination-link">
                                    Siguiente <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal para ver detalles de la factura -->
    <div id="viewFacturaModal" class="modal hidden">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Detalle de Factura <span id="view-numero-factura" class="text-accent-color"></span></h2>
                <button class="modal-close" onclick="closeModal('viewFacturaModal')">&times;</button>
            </div>
            <div class="modal-body" id="viewFacturaBody">
                <!-- Contenido dinámico via JS -->
            </div>
        </div>
    </div>

    <!-- Modal para registrar pago -->
    <div id="registerPaymentModal" class="modal hidden">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h2>Registrar Pago</h2>
                <button class="modal-close" onclick="closeModal('registerPaymentModal')">&times;</button>
            </div>
            <form id="paymentForm" method="POST" action="ventas.php" class="modal-form">
                <input type="hidden" name="action" value="registrar_pago">
                <input type="hidden" name="factura_id" id="payment-factura-id">
                <div class="form-group">
                    <label for="monto_pago">Monto a Pagar</label>
                    <input type="number" name="monto_pago" id="payment-monto" step="0.01" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="forma_pago">Forma de Pago</label>
                    <select name="forma_pago" class="form-control" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fecha_pago">Fecha del Pago</label>
                    <input type="date" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" class="form-control"
                        required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary"
                        onclick="closeModal('registerPaymentModal')">Cancelar</button>
                    <button type="submit" class="btn-primary">Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="confirmationModal" class="modal hidden">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h2 id="confirmationTitle">Confirmar acción</h2>
                <button class="modal-close" onclick="closeModal('confirmationModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmationMessage">¿Estás seguro de que quieres realizar esta acción?</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('confirmationModal')">Cancelar</button>
                <button type="button" class="btn-danger" id="confirmationConfirm">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Toast notifications container -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
    // Funciones para manejo de modales
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        toastContainer.appendChild(toast);
        
        // Remove toast after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    function showConfirmationModal(title, message, confirmCallback) {
        document.getElementById('confirmationTitle').textContent = title;
        document.getElementById('confirmationMessage').textContent = message;
        
        const confirmBtn = document.getElementById('confirmationConfirm');
        // Remove any existing event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', function() {
            closeModal('confirmationModal');
            confirmCallback();
        });
        
        openModal('confirmationModal');
    }

    function verFactura(id) {
        // Show loading state
        document.getElementById('viewFacturaBody').innerHTML = '<div class="spinner"></div>';
        openModal('viewFacturaModal');
        
        fetch(`ventas.php?action=get_factura_details&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('viewFacturaBody').innerHTML = `
                        <div class="text-center text-danger p-4">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p>${data.error}</p>
                        </div>
                    `;
                    return;
                }
                
                const body = document.getElementById('viewFacturaBody');

                // Formateadores
                const formatDate = fecha => fecha ? new Date(fecha).toLocaleDateString('es-PA') : '';
                const formatCurrency = valor => `$${parseFloat(valor || 0).toFixed(2)}`;

                // Emisor y cliente
                const emisor = {
                    nombre: 'FerreGest360',
                    direccion: 'Dirección de la empresa',
                    nif: 'NIF/ID Empresa',
                    ciudad: 'Ciudad, País',
                    email: 'info@ferregest360.com'
                };
                const cliente = {
                    nombre: data.cliente_nombre || 'Cliente',
                    direccion: data.direccion || '',
                    cedula: data.cedula_ruc || '',
                    ciudad: '',
                    email: data.email || ''
                };

                // Totales
                const subtotal = formatCurrency(data.subtotal_calc);
                const descuento = formatCurrency(data.descuento_calc);
                const itbms = formatCurrency(data.itbms_calc);
                const total = formatCurrency(data.total_calc);

                // Productos
                let itemsHtml = data.items.map(item => `
                    <tr>
                        <td class="text-center">${item.cantidad}</td>
                        <td>${item.producto_nombre} (${item.codigo})</td>
                        <td class="text-right">${formatCurrency(item.precio_unitario)}</td>
                        <td class="text-right">${formatCurrency(item.subtotal)}</td>
                    </tr>
                `).join('');

                // Pagos realizados
                let pagosHtml = '';
                if (data.pagos && data.pagos.length > 0) {
                    pagosHtml = `
                        <div class="mt-5">
                            <h4 class="font-weight-bold mb-3">Pagos Realizados</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered small">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Monto</th>
                                            <th>Forma de Pago</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.pagos.map(pago => `
                                            <tr>
                                                <td>${formatDate(pago.fecha_pago)}</td>
                                                <td class="text-right">${formatCurrency(pago.monto)}</td>
                                                <td>${pago.forma_pago}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }

                // HTML Final
                body.innerHTML = `
                    <div class="container bg-white p-4 rounded shadow-sm small">
                        <!-- Encabezado -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <h1 class="text-xl font-bold text-primary">Factura</h1>
                                <h1 class="h5 mb-3">${emisor.nombre}</h1>
                                <p><strong>Fecha de factura:</strong> ${formatDate(data.fecha_factura)}</p>
                                <p><strong>Número de factura:</strong> ${data.numero_factura}</p>
                                <p><strong>Fecha de vencimiento:</strong> ${formatDate(data.fecha_vencimiento)}</p>
                            </div>
                            <div class="col-md-4 text-right">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?data=https://tufactura.com/ver/${data.numero_factura}&size=100x100" alt="QR Factura" style="height: 100px;">
                            </div>
                        </div>

                        <!-- Datos de emisor y cliente -->
                        <div class="row pt-4 border-top">
                            <div class="col-md-6 mb-3">
                                <h5 class="text-primary font-weight-bold mb-2">${emisor.nombre}</h5>
                                <p><strong>Dirección:</strong> ${emisor.direccion}, ${emisor.ciudad}</p>
                                <p><strong>NIF:</strong> ${emisor.nif}</p>
                                <p><strong>Email:</strong> ${emisor.email}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h5 class="text-primary font-weight-bold mb-2">${cliente.nombre}</h5>
                                <p><strong>Dirección:</strong> ${cliente.direccion}</p>
                                <p><strong>Cédula/RUC:</strong> ${cliente.cedula}</p>
                                <p><strong>Ciudad:</strong> ${cliente.ciudad}</p>
                                <p><strong>Email:</strong> ${cliente.email}</p>
                            </div>
                        </div>

                        <!-- Tabla de productos -->
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered small">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">Cantidad</th>
                                        <th>Descripción</th>
                                        <th class="text-right">Precio Unitario</th>
                                        <th class="text-right">Importe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                </tbody>
                            </table>
                        </div>

                        <!-- Totales -->
                        <div class="text-right small mt-3">
                            <p><strong>Subtotal:</strong> ${subtotal}</p>
                            <p><strong>Descuento:</strong> ${descuento}</p>
                            <p><strong>ITBMS:</strong> ${itbms}</p>
                            <p class="h6 font-weight-bold pt-2">Total a pagar: ${total}</p>
                        </div>

                        ${pagosHtml}

                        <!-- Condiciones -->
                        <hr>
                        <div class="text-sm mt-4">
                            <h4 class="font-weight-bold mb-1">Condiciones y forma de pago</h4>
                            <p>El pago se realizará en un plazo de 15 días</p>
                            <p class="mt-2">Banco Santander</p>
                            <p>IBAN: ES12 3456 7891</p>
                            <p>SWIFT/BIC: ABCDESM1XXX</p>
                        </div>

                        <!-- Firma -->
                        <div class="text-right mt-4">
                            <p class="italic">${data.vendedor_nombre || 'Vendedor'}</p>
                        </div>
                    </div>
                `;

                document.getElementById('view-numero-factura').innerText = data.numero_factura;
            })
            .catch(error => {
                document.getElementById('viewFacturaBody').innerHTML = `
                    <div class="text-center text-danger p-4">
                        <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                        <p>Error al cargar los detalles de la factura: ${error.message}</p>
                    </div>
                `;
            });
    }

    function abrirModalPago(id, total) {
        document.getElementById('payment-factura-id').value = id;
        document.getElementById('payment-monto').value = total;
        openModal('registerPaymentModal');
    }

    function anularFactura(id, numero) {
        showConfirmationModal(
            'Anular Factura',
            `¿Estás seguro de que quieres anular la factura "${numero}"? Esta acción no se puede deshacer.`,
            () => {
                window.location.href = `ventas.php?action=anular&id=${id}`;
            }
        );
    }

    function imprimirFactura(id) {
        // First open the view modal
        verFactura(id);
        
        // After a short delay, trigger print
        setTimeout(() => {
            window.print();
        }, 1000);
    }

    function crearNuevaFactura() {
        window.location.href = 'crear_factura.php';
    }

    // Initialize dropdown menus
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const menu = this.nextElementSibling;
                const isOpen = menu.style.display === 'block';
                
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(m => {
                    m.style.display = 'none';
                });
                
                // Toggle this dropdown
                menu.style.display = isOpen ? 'none' : 'block';
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        });
        
        // Prevent dropdowns from closing when clicking inside them
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
        
        // Show any PHP messages as toasts
        <?php if (isset($_SESSION['success_message'])): ?>
            showToast('<?php echo $_SESSION['success_message']; ?>', 'success');
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            showToast('<?php echo $_SESSION['error_message']; ?>', 'error');
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>
