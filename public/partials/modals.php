<!-- Modal for Add Product -->
<div id="addProductModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-plus"></i> Agregar Nuevo Producto</h2>
            <button class="modal-close" onclick="closeModal('addProductModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formCliente" class="modal-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="productCode">Código del Producto</label>
                    <input type="text" id="productCode" name="productCode" required>
                </div>
                <div class="form-group">
                    <label for="productBarcode">Código de Barras</label>
                    <input type="text" id="productBarcode" name="productBarcode">
                </div>
            </div>

            <div class="form-group">
                <label for="productName">Nombre del Producto</label>
                <input type="text" id="productName" name="productName" required>
            </div>

            <div class="form-group">
                <label for="productDescription">Descripción</label>
                <textarea id="productDescription" name="productDescription" rows="3"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="productCategory">Categoría</label>
                    <select id="productCategory" name="productCategory">
                        <option value="">Seleccionar categoría</option>
                        <option value="1">Herramientas</option>
                        <option value="2">Ferretería</option>
                        <option value="3">Plomería</option>
                        <option value="4">Electricidad</option>
                        <option value="5">Construcción</option>
                        <option value="6">Pintura</option>
                        <option value="7">Jardín</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="productBrand">Marca</label>
                    <select id="productBrand" name="productBrand">
                        <option value="">Seleccionar marca</option>
                        <option value="1">Stanley</option>
                        <option value="2">Black & Decker</option>
                        <option value="3">Truper</option>
                        <option value="4">DeWalt</option>
                        <option value="5">Makita</option>
                        <option value="6">Sherwin Williams</option>
                        <option value="7">Genérica</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="purchasePrice">Precio de Compra</label>
                    <input type="number" id="purchasePrice" name="purchasePrice" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label for="salePrice">Precio de Venta</label>
                    <input type="number" id="salePrice" name="salePrice" step="0.01" min="0" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="minStock">Stock Mínimo</label>
                    <input type="number" id="minStock" name="minStock" min="0" value="0">
                </div>
                <div class="form-group">
                    <label for="initialStock">Stock Inicial</label>
                    <input type="number" id="initialStock" name="initialStock" min="0" value="0">
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('addProductModal')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Add Client -->
<div id="addClientModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus"></i> Registrar Cliente</h2>
            <button class="modal-close" onclick="closeModal('addClientModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form class="modal-form" method="POST" action="/public/guardar_cliente.php">
            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="cedula">Cédula</label>
                    <input type="text" id="cedula" name="cedula">
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono">
                </div>
            </div>
            <div class="form-group">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo">
            </div>
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <textarea id="direccion" name="direccion" rows="2"></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('addClientModal')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Add Provider -->
<div id="addProviderModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-truck"></i> Registrar Proveedor</h2>
            <button class="modal-close" onclick="closeModal('addProviderModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form class="modal-form" method="POST" action="/public/guardar_proveedor.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="prov_nombre">Nombre Proveedor</label>
                    <input type="text" id="prov_nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="prov_razon_social">Razón Social</label>
                    <input type="text" id="prov_razon_social" name="razon_social">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="prov_ruc">RUC</label>
                    <input type="text" id="prov_ruc" name="ruc" required>
                </div>
                <div class="form-group">
                    <label for="prov_telefono">Teléfono Principal</label>
                    <input type="text" id="prov_telefono" name="telefono_principal">
                </div>
            </div>
            <div class="form-group">
                <label for="prov_email">Email</label>
                <input type="email" id="prov_email" name="email">
            </div>
            <div class="form-group">
                <label for="prov_direccion">Dirección</label>
                <textarea id="prov_direccion" name="direccion" rows="2"></textarea>
            </div>
            <hr>
            <h4>Contacto</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="prov_nombre_contacto">Nombre Contacto</label>
                    <input type="text" id="prov_nombre_contacto" name="nombre_contacto">
                </div>
                <div class="form-group">
                    <label for="prov_telefono_contacto">Teléfono Contacto</label>
                    <input type="text" id="prov_telefono_contacto" name="telefono_contacto">
                </div>
            </div>
            <div class="form-group">
                <label for="prov_email_contacto">Email Contacto</label>
                <input type="email" id="prov_email_contacto" name="email_contacto">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('addProviderModal')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Proveedor
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Confirmation -->
<div id="confirmationModal" class="modal hidden">
    <div class="modal-content small">
        <div class="modal-header">
            <h2 id="confirmationModalTitle">Confirmar Acción</h2>
            <button class="modal-close" onclick="closeModal('confirmationModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="confirmationModalText">¿Estás seguro de que quieres realizar esta acción?</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeModal('confirmationModal')">
                Cancelar
            </button>
            <button type="button" id="confirmActionBtn" class="btn-danger">
                Confirmar
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
        document.getElementById('formCliente').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = e.target;
            const datos = new FormData(form);

            fetch('guardar_cliente.php', {
                    method: 'POST',
                    body: datos
                })
                .then(res => res.text())
                .then(respuesta => {
                    alert(respuesta); // Muestra mensaje de guardado
                    closeModal('addClientModal');
                    form.reset();
                    cargarClientes(); // Recargar la tabla de clientes
                })
                .catch(err => {
                    alert("Error al guardar el cliente.");
                    console.error(err);
                });
        });

        fetch('listar_clientes.php')
            .then(res => res.text())
            .then(html => {
                document.getElementById('clientesTabla').innerHTML = html;
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarClientes();
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