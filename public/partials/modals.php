<?php
// Incluir conexión a la base de datos
require_once '../config/connection.php';

// Cargar datos para los selects de los modales
try {
    $conn = getDBConnection();

    // Categorías
    $stmt_categorias = $conn->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");
    $categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

    // Marcas
    $stmt_marcas = $conn->query("SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre");
    $marcas = $stmt_marcas->fetchAll(PDO::FETCH_ASSOC);

    // Proveedores
    $stmt_proveedores = $conn->query("SELECT id, nombre FROM proveedores WHERE activo = 1 ORDER BY nombre");
    $proveedores = $stmt_proveedores->fetchAll(PDO::FETCH_ASSOC);

    // Unidades de Medida
    $stmt_unidades = $conn->query("SELECT id, nombre, abreviatura FROM unidades_medida ORDER BY nombre");
    $unidades = $stmt_unidades->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Manejo de error básico
    error_log("Error al cargar datos para modales: " . $e->getMessage());
    // Inicializar arrays vacíos para evitar errores en el renderizado
    $categorias = $marcas = $proveedores = $unidades = [];
}
?>

<!-- Modal for Add Product  en productos-->
<div id="addProductModal" class="modal hidden">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2><i class="fas fa-plus"></i> Registrar Nuevo Producto</h2>
            <button class="modal-close" onclick="closeModal('addProductModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formProducto" class="modal-form" method="POST" action="productos.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="agregar">

            <!-- Información Básica del Producto -->
            <div class="form-section">
                <h3><i class="fas fa-box"></i> Información Básica</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="codigo">Código del Producto *</label>
                        <input type="text" id="codigo" name="codigo" required placeholder="Ej: PROD001">
                    </div>
                    <div class="form-group">
                        <label for="codigo_barras">Código de Barras</label>
                        <input type="text" id="codigo_barras" name="codigo_barras" placeholder="Ej: 1234567890123"
                            maxlength="13">
                    </div>
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre del Producto *</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej: Martillo de 16 oz Stanley">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3"
                        placeholder="Descripción detallada del producto"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="categoria_id">Categoría</label>
                        <select id="categoria_id" name="categoria_id">
                            <option value="">Seleccionar categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>">
                                    <?php echo htmlspecialchars($categoria['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="marca_id">Marca</label>
                        <select id="marca_id" name="marca_id">
                            <option value="">Seleccionar marca</option>
                            <?php foreach ($marcas as $marca): ?>
                                <option value="<?php echo $marca['id']; ?>">
                                    <?php echo htmlspecialchars($marca['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

            </div>

            <!-- Información de Precios -->
            <div class="form-section">
                <h3><i class="fas fa-dollar-sign"></i> Información de Precios</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="precio_compra">Precio de Compra ($)</label>
                        <input type="number" id="precio_compra" name="precio_compra" step="0.01" min="0" value="0.00"
                            placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label for="precio_venta">Precio de Venta ($) *</label>
                        <input type="number" id="precio_venta" name="precio_venta" step="0.01" min="0" required
                            placeholder="0.00">
                    </div>
                </div>
            </div>

            <!-- Información de Inventario -->
            <div class="form-section">
                <h3><i class="fas fa-warehouse"></i> Información de Inventario</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="stock_minimo">Stock Mínimo</label>
                        <input type="number" id="stock_minimo" name="stock_minimo" min="0" value="5" placeholder="5">
                    </div>
                    <div class="form-group">
                        <label for="stock_inicial">Stock Inicial</label>
                        <input type="number" id="stock_inicial" name="stock_inicial" min="0" value="0" placeholder="0">
                    </div>

                    <div class="form-group">
                        <label for="ubicacion">Ubicacion</label>
                        <input type="text" id="ubicacion" name="ubicacion" placeholder="Ubicacion de Almacenamiento">
                    </div>
                    <div class="form-group">
                        <label for="imagen_upload">Cargar Imagen</label>
                        <input type="file" id="imagen_upload" name="imagen_upload" accept="image/*" placeholder="">
                    </div>
                    <div class="form-group">
                        <label for="imagen_url">URL de la Imagen</label>
                        <input type="url" id="imagen_url" name="imagen_url" placeholder="https://ejemplo.com/imagen.jpg">
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('addProductModal')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Edit Product -->
<div id="editProductModal" class="modal hidden">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2><i class="fas fa-edit"></i> Editar Producto</h2>
            <button class="modal-close" onclick="closeModal('editProductModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formEditProducto" class="modal-form" method="POST" action="productos.php">
            <input type="hidden" name="action" value="editar">
            <input type="hidden" id="edit_producto_id" name="producto_id">

            <!-- Información Básica del Producto -->
            <div class="form-section">
                <h3><i class="fas fa-box"></i> Información Básica</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_codigo">Código del Producto *</label>
                        <input type="text" id="edit_codigo" name="codigo" required placeholder="Ej: PROD001">
                    </div>
                    <div class="form-group">
                        <label for="edit_codigo_barras">Código de Barras</label>
                        <input type="text" id="edit_codigo_barras" name="codigo_barras" placeholder="Ej: 1234567890123"
                            maxlength="13">
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_nombre">Nombre del Producto *</label>
                    <input type="text" id="edit_nombre" name="nombre" required
                        placeholder="Ej: Martillo de 16 oz Stanley">
                </div>

                <div class="form-group">
                    <label for="edit_descripcion">Descripción</label>
                    <textarea id="edit_descripcion" name="descripcion" rows="3"
                        placeholder="Descripción detallada del producto"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_categoria_id">Categoría</label>
                        <select id="edit_categoria_id" name="categoria_id">
                            <option value="">Seleccionar categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>">
                                    <?php echo htmlspecialchars($categoria['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_marca_id">Marca</label>
                        <select id="edit_marca_id" name="marca_id">
                            <option value="">Seleccionar marca</option>
                            <?php foreach ($marcas as $marca): ?>
                                <option value="<?php echo $marca['id']; ?>">
                                    <?php echo htmlspecialchars($marca['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Información de Precios -->
            <div class="form-section">
                <h3><i class="fas fa-dollar-sign"></i> Información de Precios</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_precio_compra">Precio de Compra ($)</label>
                        <input type="number" id="edit_precio_compra" name="precio_compra" step="0.01" min="0"
                            value="0.00" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label for="edit_precio_venta">Precio de Venta ($) *</label>
                        <input type="number" id="edit_precio_venta" name="precio_venta" step="0.01" min="0" required
                            placeholder="0.00">
                    </div>
                </div>
            </div>

            <!-- Información de Inventario -->
            <div class="form-section">
                <h3><i class="fas fa-warehouse"></i> Información de Inventario</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_stock_minimo">Stock Mínimo</label>
                        <input type="number" id="edit_stock_minimo" name="stock_minimo" min="0" value="5"
                            placeholder="5">
                    </div>
                    <div class="form-group">
                        <label for="edit_stock_actual">Stock Actual</label>
                        <input type="number" id="edit_stock_actual" readonly placeholder="0"
                            style="background-color: #f5f5f5;">
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('editProductModal')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Actualizar Producto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Adjust Stock -->
<div id="adjustStockModal" class="modal hidden">
    <div class="modal-content modal-medium">
        <div class="modal-header">
            <h2><i class="fas fa-warehouse"></i> Ajustar Stock</h2>
            <button class="modal-close" onclick="closeModal('adjustStockModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formAdjustStock" class="modal-form" method="POST" action="productos.php">
            <input type="hidden" name="action" value="ajustar_stock">
            <input type="hidden" id="adjust_producto_id" name="producto_id">

            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Información del Producto</h3>

                <div class="form-group">
                    <label>Producto</label>
                    <input type="text" id="adjust_producto_nombre" readonly style="background-color: #f5f5f5;">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Stock Actual</label>
                        <input type="number" id="adjust_stock_actual" readonly style="background-color: #f5f5f5;">
                    </div>
                    <div class="form-group">
                        <label for="adjust_nuevo_stock">Nuevo Stock *</label>
                        <input type="number" id="adjust_nuevo_stock" name="nuevo_stock" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="adjust_motivo">Motivo del Ajuste *</label>
                    <textarea id="adjust_motivo" name="motivo" rows="3" required
                        placeholder="Ej: Inventario físico, Daños, Pérdidas, etc."></textarea>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('adjustStockModal')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Ajustar Stock
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Add Client -->
<div id="addClientModal" class="modal hidden">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus"></i> Registrar Nuevo Cliente</h2>
            <button class="modal-close" onclick="closeModal('addClientModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formCliente" class="modal-form" method="POST" action="clientes.php">
            <input type="hidden" name="action" value="agregar_cliente">

            <!-- Información Básica -->
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Información Básica</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_cliente">Tipo de Cliente *</label>
                        <select id="tipo_cliente" name="tipo_cliente" required onchange="toggleClienteFields()">
                            <option value="">Seleccionar tipo</option>
                            <option value="natural">Persona Natural</option>
                            <option value="juridico">Persona Jurídica</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" placeholder="Se generará automáticamente" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" id="nombre_grupo">
                        <label for="nombre">Nombre Completo *</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Carlos Pérez">
                    </div>

                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cedula_ruc">Cédula/RUC *</label>
                        <input type="text" id="cedula_ruc" name="cedula_ruc" required
                            placeholder="Ej: 8-123-456 o 12345678-1-123456" maxlength="30">
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" placeholder="Ej: 507-123-4567" maxlength="25">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" placeholder="Ej: cliente@email.com">
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <textarea id="direccion" name="direccion" rows="2"
                        placeholder="Dirección completa del cliente"></textarea>
                </div>
            </div>

            <!-- Información de Crédito -->
            <div class="form-section">
                <h3><i class="fas fa-credit-card"></i> Información de Crédito</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="limite_credito">Límite de Crédito ($)</label>
                        <input type="number" id="limite_credito" name="limite_credito" step="0.01" min="0" value="0.00"
                            placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label for="dias_credito">Días de Crédito</label>
                        <input type="number" id="dias_credito" name="dias_credito" min="0" value="0" placeholder="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="descuento_porcentaje">Descuento (%)</label>
                    <input type="number" id="descuento_porcentaje" name="descuento_porcentaje" step="0.01" min="0"
                        max="100" value="0.00" placeholder="0.00">
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="form-section">
                <h3><i class="fas fa-cog"></i> Información Adicional</h3>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="3"
                        placeholder="Información adicional sobre el cliente"></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="activo" name="activo" value="1" checked>
                        <span class="checkmark"></span>
                        Cliente Activo
                    </label>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('addClientModal')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Edit Client -->
<div id="editClientModal" class="modal hidden">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2><i class="fas fa-user-edit"></i> Editar Cliente</h2>
            <button class="modal-close" onclick="closeModal('editClientModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formEditCliente" class="modal-form" method="POST" action="clientes.php">
            <input type="hidden" name="action" value="editar_cliente">
            <input type="hidden" id="edit_cliente_id" name="cliente_id">

            <!-- Información Básica -->
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Información Básica</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_tipo_cliente">Tipo de Cliente *</label>
                        <select id="edit_tipo_cliente" name="tipo_cliente" required
                            onchange="toggleEditClienteFields()">
                            <option value="">Seleccionar tipo</option>
                            <option value="natural">Persona Natural</option>
                            <option value="juridico">Persona Jurídica</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_codigo">Código</label>
                        <input type="text" id="edit_codigo" name="codigo" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" id="edit_nombre_grupo">
                        <label for="edit_nombre">Nombre Completo *</label>
                        <input type="text" id="edit_nombre" name="nombre" required placeholder="Ej: Juan Carlos Pérez">
                    </div>
                    <div class="form-group" id="edit_razon_social_grupo" style="display: none;">
                        <label for="edit_razon_social">Razón Social *</label>
                        <input type="text" id="edit_razon_social" name="razon_social"
                            placeholder="Ej: Constructora ABC S.A.">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_cedula_ruc">Cédula/RUC *</label>
                        <input type="text" id="edit_cedula_ruc" name="cedula_ruc" required
                            placeholder="Ej: 8-123-456 o 12345678-1-123456" pattern="[0-9\-]+" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="edit_telefono">Teléfono</label>
                        <input type="tel" id="edit_telefono" name="telefono" placeholder="Ej: 507-123-4567"
                            pattern="[0-9\-]+" maxlength="15">
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_email">Correo Electrónico</label>
                    <input type="email" id="edit_email" name="email" placeholder="Ej: cliente@email.com">
                </div>

                <div class="form-group">
                    <label for="edit_direccion">Dirección</label>
                    <textarea id="edit_direccion" name="direccion" rows="2"
                        placeholder="Dirección completa del cliente"></textarea>
                </div>
            </div>

            <!-- Información de Crédito -->
            <div class="form-section">
                <h3><i class="fas fa-credit-card"></i> Información de Crédito</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_limite_credito">Límite de Crédito ($)</label>
                        <input type="number" id="edit_limite_credito" name="limite_credito" step="0.01" min="0"
                            value="0.00" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label for="edit_dias_credito">Días de Crédito</label>
                        <input type="number" id="edit_dias_credito" name="dias_credito" min="0" value="0"
                            placeholder="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_descuento_porcentaje">Descuento (%)</label>
                    <input type="number" id="edit_descuento_porcentaje" name="descuento_porcentaje" step="0.01" min="0"
                        max="100" value="0.00" placeholder="0.00">
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="form-section">
                <h3><i class="fas fa-cog"></i> Información Adicional</h3>

                <div class="form-group">
                    <label for="edit_observaciones">Observaciones</label>
                    <textarea id="edit_observaciones" name="observaciones" rows="3"
                        placeholder="Información adicional sobre el cliente"></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_activo" name="activo" value="1">
                        <span class="checkmark"></span>
                        Cliente Activo
                    </label>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('editClientModal')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Actualizar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Client History -->
<div id="clientHistoryModal" class="modal hidden">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2><i class="fas fa-history"></i> Historial de Compras</h2>
            <button class="modal-close" onclick="closeModal('clientHistoryModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-body">
            <div id="clientHistoryContent">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando historial...</p>
                </div>
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeModal('clientHistoryModal')">
                <i class="fas fa-times"></i> Cerrar
            </button>
        </div>
    </div>
</div>

<!-- Modal for Add Provider -->
<div id="addProviderModal" class="modal hidden">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2><i class="fas fa-truck"></i> Registrar Nuevo Proveedor</h2>
            <button class="modal-close" onclick="closeModal('addProviderModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formProveedor" class="modal-form" method="POST" action="proveedor.php">
            <!-- Información Básica del Proveedor -->
            <div class="form-section">
                <h3><i class="fas fa-building"></i> Información Básica</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" placeholder="Se generará automáticamente" readonly>
                    </div>
                    <div class="form-group">
                        <label for="tipo_proveedor">Tipo de Proveedor</label>
                        <select id="tipo_proveedor" name="tipo_proveedor">
                            <option value="">Seleccionar tipo</option>
                            <option value="distribuidor">Distribuidor</option>
                            <option value="fabricante">Fabricante</option>
                            <option value="importador">Importador</option>
                            <option value="mayorista">Mayorista</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre del Proveedor *</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Distribuidora ABC">
                    </div>
                    <div class="form-group">
                        <label for="razon_social">Razón Social</label>
                        <input type="text" id="razon_social" name="razon_social"
                            placeholder="Ej: Distribuidora ABC S.A.">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ruc">RUC *</label>
                        <input type="text" id="ruc" name="ruc" required placeholder="Ej: 12345678-1-123456"
                            pattern="[0-9\-]+" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="telefono_principal">Teléfono Principal</label>
                        <input type="tel" id="telefono_principal" name="telefono_principal"
                            placeholder="Ej: 507-123-4567" pattern="[0-9\-]+" maxlength="15">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" placeholder="Ej: info@proveedor.com">
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <textarea id="direccion" name="direccion" rows="2"
                        placeholder="Dirección completa del proveedor"></textarea>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Información de Contacto</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_contacto">Nombre del Contacto</label>
                        <input type="text" id="nombre_contacto" name="nombre_contacto" placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="form-group">
                        <label for="cargo_contacto">Cargo del Contacto</label>
                        <input type="text" id="cargo_contacto" name="cargo_contacto"
                            placeholder="Ej: Gerente de Ventas">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="telefono_contacto">Teléfono del Contacto</label>
                        <input type="tel" id="telefono_contacto" name="telefono_contacto" placeholder="Ej: 507-234-5678"
                            pattern="[0-9\-]+" maxlength="15">
                    </div>
                    <div class="form-group">
                        <label for="email_contacto">Email del Contacto</label>
                        <input type="email" id="email_contacto" name="email_contacto"
                            placeholder="Ej: juan.perez@proveedor.com">
                    </div>
                </div>
            </div>

            <!-- Información Comercial -->
            <div class="form-section">
                <h3><i class="fas fa-handshake"></i> Información Comercial</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dias_credito">Días de Crédito</label>
                        <input type="number" id="dias_credito" name="dias_credito" min="0" value="0" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="descuento_porcentaje">Descuento (%)</label>
                        <input type="number" id="descuento_porcentaje" name="descuento_porcentaje" step="0.01" min="0"
                            max="100" value="0.00" placeholder="0.00">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tiempo_entrega">Tiempo de Entrega (días)</label>
                        <input type="number" id="tiempo_entrega" name="tiempo_entrega" min="0" value="7"
                            placeholder="7">
                    </div>
                    <div class="form-group">
                        <label for="monto_minimo">Monto Mínimo de Pedido ($)</label>
                        <input type="number" id="monto_minimo" name="monto_minimo" step="0.01" min="0" value="0.00"
                            placeholder="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label for="condiciones_pago">Condiciones de Pago</label>
                    <textarea id="condiciones_pago" name="condiciones_pago" rows="2"
                        placeholder="Condiciones especiales de pago"></textarea>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="form-section">
                <h3><i class="fas fa-cog"></i> Información Adicional</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="sitio_web">Sitio Web</label>
                        <input type="url" id="sitio_web" name="sitio_web" placeholder="Ej: https://www.proveedor.com">
                    </div>
                    <div class="form-group">
                        <label for="horario_atencion">Horario de Atención</label>
                        <input type="text" id="horario_atencion" name="horario_atencion"
                            placeholder="Ej: L-V 8:00 AM - 6:00 PM">
                    </div>
                </div>

                <div class="form-group">
                    <label for="productos_principales">Productos Principales</label>
                    <textarea id="productos_principales" name="productos_principales" rows="3"
                        placeholder="Lista de productos principales que suministra"></textarea>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="3"
                        placeholder="Información adicional sobre el proveedor"></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="activo" name="activo" value="1" checked>
                        <span class="checkmark"></span>
                        Proveedor Activo
                    </label>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('addProviderModal')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Proveedor
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Add Purchase -->
<div id="addPurchaseModal" class="modal hidden">
    <div class="modal-content modal-extra-large">
        <div class="modal-header">
            <h2><i class="fas fa-shopping-cart"></i> Registrar Nueva Compra</h2>
            <button class="modal-close" onclick="closeModal('addPurchaseModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formCompra" class="modal-form" method="POST" action="compra.php">
            <!-- Información Básica de la Compra -->
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Información de la Compra</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="codigo">Código de Compra</label>
                        <input type="text" id="codigo" name="codigo" placeholder="Se generará automáticamente" readonly>
                    </div>
                    <div class="form-group">
                        <label for="fecha_compra">Fecha de Compra *</label>
                        <input type="datetime-local" id="fecha_compra" name="fecha_compra" required
                            value="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="proveedor_id">Proveedor *</label>
                        <select id="proveedor_id" name="proveedor_id" required onchange="cargarInfoProveedor()">
                            <option value="">Seleccionar proveedor</option>
                            <!-- Se cargará dinámicamente -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="numero_factura">Número de Factura</label>
                        <input type="text" id="numero_factura" name="numero_factura"
                            placeholder="Ej: F001-001-00012345">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_entrega">Fecha de Entrega Esperada</label>
                        <input type="date" id="fecha_entrega" name="fecha_entrega"
                            value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="condiciones_pago">Condiciones de Pago</label>
                        <select id="condiciones_pago" name="condiciones_pago">
                            <option value="contado">Contado</option>
                            <option value="credito_15">Crédito 15 días</option>
                            <option value="credito_30">Crédito 30 días</option>
                            <option value="credito_45">Crédito 45 días</option>
                            <option value="credito_60">Crédito 60 días</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="2"
                        placeholder="Observaciones adicionales sobre la compra"></textarea>
                </div>
            </div>

            <!-- Productos de la Compra -->
            <div class="form-section">
                <h3><i class="fas fa-boxes"></i> Productos de la Compra</h3>

                <div class="products-table-container">
                    <table id="productosTable" class="products-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Descuento %</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="productosTableBody">
                            <!-- Se llenará dinámicamente -->
                        </tbody>
                    </table>

                    <div class="add-product-section">
                        <button type="button" class="btn-secondary" onclick="agregarFilaProducto()">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                    </div>
                </div>
            </div>

            <!-- Resumen de la Compra -->
            <div class="form-section">
                <h3><i class="fas fa-calculator"></i> Resumen de la Compra</h3>

                <div class="summary-grid">
                    <div class="summary-item">
                        <label>Subtotal:</label>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="summary-item">
                        <label>Descuentos:</label>
                        <span id="total_descuentos">$0.00</span>
                    </div>
                    <div class="summary-item">
                        <label>Impuestos:</label>
                        <span id="total_impuestos">$0.00</span>
                    </div>
                    <div class="summary-item total">
                        <label>Total:</label>
                        <span id="total_compra">$0.00</span>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('addPurchaseModal')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Compra
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Purchase Detail -->
<div id="purchaseDetailModal" class="modal hidden">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2><i class="fas fa-eye"></i> Detalle de Compra</h2>
            <button class="modal-close" onclick="closeModal('purchaseDetailModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="purchaseDetailContent" class="modal-body">
            <!-- Se cargará dinámicamente -->
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeModal('purchaseDetailModal')">
                <i class="fas fa-times"></i> Cerrar
            </button>
            <button type="button" class="btn-primary" onclick="imprimirCompra()">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<!-- Modal for Product Details -->
<div id="productDetailModal" class="modal hidden">
    <div class="modal-content modal-large details-products" style="max-width: 1000px;">
        <div class="modal-header">
            <h2><i class="fas fa-eye"></i> Detalle del Producto</h2>
            <button class="modal-close" onclick="closeModal('productDetailModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="productDetailContent">
            <!-- Content will be loaded dynamically -->
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando detalles...</p>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeModal('productDetailModal')">Cerrar</button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="modal hidden">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2 id="confirmationTitle">Confirmar Acción</h2>
            <button class="modal-close" onclick="closeModal('confirmationModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="confirmationMessage">¿Estás seguro de que quieres realizar esta acción?</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeModal('confirmationModal')">
                Cancelar
            </button>
            <button type="button" class="btn-danger" id="confirmationConfirm">
                Confirmar
            </button>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="modal hidden">
    <div class="modal-content modal-small">
        <div class="modal-body text-center">
            <div class="loading-spinner"></div>
            <h3>Procesando...</h3>
            <p id="loadingMessage">Por favor espera mientras se procesa tu solicitud.</p>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal hidden">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2><i class="fas fa-check-circle text-success"></i> Éxito</h2>
            <button class="modal-close" onclick="closeModal('successModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="successMessage">La operación se completó exitosamente.</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-primary" onclick="closeModal('successModal')">
                Aceptar
            </button>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="modal hidden">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2><i class="fas fa-exclamation-triangle text-danger"></i> Error</h2>
            <button class="modal-close" onclick="closeModal('errorModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="errorMessage">Ha ocurrido un error. Por favor intenta nuevamente.</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeModal('errorModal')">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
    // Funciones de modal
    function showAddClientModal() {
        document.getElementById('addClientModal').classList.remove('hidden');
    }

    function showAddProductModal() {
        document.getElementById('addProductModal').classList.remove('hidden');
    }

    function showAddProviderModal() {
        document.getElementById('addProviderModal').classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.add('hidden');
        }
    });

    function cargarClientes() {
        // Envío del formulario de clientes con AJAX
        const formCliente = document.getElementById('formCliente');
        if (formCliente) {
            formCliente.addEventListener('submit', function(e) {
                e.preventDefault();

                const form = e.target;
                const datos = new FormData(form);

                fetch('clientes.php', {
                        method: 'POST',
                        body: datos
                    })
                    .then(res => res.text())
                    .then(respuesta => {
                        alert(respuesta); // Muestra mensaje de guardado
                        closeModal('addClientModal');
                        form.reset();
                        // Recargar la página para mostrar los cambios
                        window.location.reload();
                    })
                    .catch(err => {
                        alert("Error al guardar el cliente.");
                        console.error(err);
                    });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarClientes();

        // Event listener para el formulario de productos
        const formProducto = document.getElementById('formProducto');
        if (formProducto) {
            formProducto.addEventListener('submit', enviarFormularioProducto);
        }

        // Event listener para el formulario de edición de productos
        const formEditProducto = document.getElementById('formEditProducto');
        if (formEditProducto) {
            formEditProducto.addEventListener('submit', enviarFormularioEditProducto);
        }

        // Event listener para el formulario de ajuste de stock
        const formAdjustStock = document.getElementById('formAdjustStock');
        if (formAdjustStock) {
            formAdjustStock.addEventListener('submit', enviarFormularioAdjustStock);
        }
    });

    // Script de Bootstrap
    // Asegúrate de que este script esté después de cargar el DOM y tus scripts personalizados
    // Si ya tienes el script en tu head, puedes omitir esto
    var scriptBootstrap = document.createElement('script');
    scriptBootstrap.src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js";
    scriptBootstrap.integrity = "sha384-qQ2iX+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo";
    scriptBootstrap.crossOrigin = "anonymous";
    document.body.appendChild(scriptBootstrap);
</script>