// Variable global para el estado del sidebar
let sidebarCollapsed = false;

/**
 * Función para alternar el estado del sidebar
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    sidebarCollapsed = !sidebarCollapsed;

    if (sidebarCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('expanded');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const navLinks = document.querySelectorAll('.nav-link');

    // Marcar la página activa en el sidebar
    function setActivePage() {
        const currentPage = window.location.pathname.split('/').pop();
        navLinks.forEach(link => {
            const linkTarget = link.getAttribute('data-target');
            const navItem = link.parentElement;

            if (linkTarget === currentPage) {
                navItem.classList.add('active');
            } else {
                navItem.classList.remove('active');
            }
        });
    }

    // Establecer página activa al cargar
    setActivePage();

    // Manejar clics en los enlaces de la barra lateral
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const targetUrl = this.getAttribute('data-target');
            if (targetUrl) {
                // Redirigir directamente a la página
                window.location.href = targetUrl;
            }
        });
    });
});

function showSection(sectionName) {
    // Redirigir a la página correspondiente
    window.location.href = sectionName + '.php';
}

function showConfirmationModal(title, text, onConfirm) {
    document.getElementById('confirmationTitle').textContent = title;
    document.getElementById('confirmationMessage').textContent = text;

    const confirmBtn = document.getElementById('confirmationConfirm');

    // Es una buena práctica clonar y reemplazar el botón para evitar acumular listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

    newConfirmBtn.onclick = function () {
        onConfirm();
        closeModal('confirmationModal');
    };

    document.getElementById('confirmationModal').classList.remove('hidden');
}

function logout() {
    showConfirmationModal(
        'Cerrar Sesión',
        '¿Estás seguro de que quieres cerrar sesión?',
        () => {
            window.location.href = 'logout.php';
        }
    );
}

// ===== FUNCIONES PARA MODALES =====

/**
 * Funciones para abrir modales específicos desde el dashboard
 */
function showAddProductModal() {
    openModal('addProductModal');
}

function showAddClientModal() {
    openModal('addClientModal');
}

function showAddProviderModal() {
    openModal('addProviderModal');
}

/**
 * Abrir modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Enfocar el primer campo de entrada
        const firstInput = modal.querySelector('input, select, textarea');
        if (firstInput) {
            firstInput.focus();
        }
    }
}

/**
 * Cerrar modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';

        // Limpiar formulario si existe
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }
    }
}

/**
 * Mostrar modal de carga
 */
function showLoadingModal(message = 'Procesando...') {
    let modal = document.getElementById('loadingModal');

    if (!modal) {
        // Crear modal dinámicamente
        modal = document.createElement('div');
        modal.id = 'loadingModal';
        modal.className = 'modal hidden';
        modal.innerHTML = `
            <div class="modal-content modal-small">
                <div class="modal-body text-center">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <h3>Procesando...</h3>
                    <p id="loadingMessage">${message}</p>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    } else {
        const loadingMessage = modal.querySelector('#loadingMessage');
        if (loadingMessage) {
            loadingMessage.textContent = message;
        }
    }

    openModal('loadingModal');
}

/**
 * Ocultar modal de carga
 */
function hideLoadingModal() {
    closeModal('loadingModal');
}

/**
 * Mostrar modal de éxito
 */
function showSuccessModal(message = 'La operación se completó exitosamente.') {
    let modal = document.getElementById('successModal');

    if (!modal) {
        // Crear modal dinámicamente
        modal = document.createElement('div');
        modal.id = 'successModal';
        modal.className = 'modal hidden';
        modal.innerHTML = `
            <div class="modal-content modal-small">
                <div class="modal-header">
                    <h2><i class="fas fa-check-circle text-success"></i> Éxito</h2>
                    <button class="modal-close" onclick="closeModal('successModal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="successMessage">${message}</p>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-primary" onclick="closeModal('successModal')">
                        Aceptar
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    } else {
        const successMessage = modal.querySelector('#successMessage');
        if (successMessage) {
            successMessage.textContent = message;
        }
    }

    openModal('successModal');
}

/**
 * Mostrar modal de error
 */
function showErrorModal(message = 'Ha ocurrido un error. Por favor intenta nuevamente.') {
    let modal = document.getElementById('errorModal');

    if (!modal) {
        // Crear modal dinámicamente
        modal = document.createElement('div');
        modal.id = 'errorModal';
        modal.className = 'modal hidden';
        modal.innerHTML = `
            <div class="modal-content modal-small">
                <div class="modal-header">
                    <h2><i class="fas fa-exclamation-triangle text-danger"></i> Error</h2>
                    <button class="modal-close" onclick="closeModal('errorModal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage">${message}</p>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('errorModal')">
                        Cerrar
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    } else {
        const errorMessage = modal.querySelector('#errorMessage');
        if (errorMessage) {
            errorMessage.textContent = message;
        }
    }

    openModal('errorModal');
}

// ===== FUNCIONES ESPECÍFICAS PARA PRODUCTOS =====

/**
 * Calcular precio de venta automáticamente
 */
function calcularPrecioVenta() {
    const precioCompra = parseFloat(document.getElementById('precio_compra')?.value || 0);
    const margenGanancia = 30; // Margen fijo del 30%

    if (precioCompra > 0) {
        const factorGanancia = 1 + (margenGanancia / 100);
        const precioVentaSugerido = precioCompra * factorGanancia;

        const precioVentaInput = document.getElementById('precio_venta');
        if (precioVentaInput) {
            precioVentaInput.value = precioVentaSugerido.toFixed(2);
        }
    }
}

/**
 * Validar formulario de producto
 */
function validarFormularioProducto() {
    const codigo = document.getElementById('codigo')?.value;
    const nombre = document.getElementById('nombre')?.value;
    const precioVenta = parseFloat(document.getElementById('precio_venta')?.value || 0);
    const codigoBarras = document.getElementById('codigo_barras')?.value;
    const precioCompra = parseFloat(document.getElementById('precio_compra')?.value || 0);
    const stockMinimo = parseFloat(document.getElementById('stock_minimo')?.value || 0);
    const stockInicial = parseFloat(document.getElementById('stock_inicial')?.value || 0);

    // Validar código
    if (!codigo || !codigo.trim()) {
        showErrorModal('El código del producto es obligatorio');
        return false;
    }

    // Validar nombre
    if (!nombre || !nombre.trim()) {
        showErrorModal('El nombre del producto es obligatorio');
        return false;
    }

    // Validar precio de venta
    if (precioVenta <= 0) {
        showErrorModal('El precio de venta debe ser mayor a cero');
        return false;
    }

    // Validar código de barras si se proporciona
    if (codigoBarras && !validarCodigoBarras(codigoBarras)) {
        showErrorModal('El código de barras debe tener 13 dígitos numéricos');
        return false;
    }

    // Validar precio de compra
    if (precioCompra < 0) {
        showErrorModal('El precio de compra no puede ser negativo');
        return false;
    }

    // Validar stock mínimo
    if (stockMinimo < 0) {
        showErrorModal('El stock mínimo no puede ser negativo');
        return false;
    }

    // Validar stock inicial
    if (stockInicial < 0) {
        showErrorModal('El stock inicial no puede ser negativo');
        return false;
    }

    return true;
}

/**
 * Enviar formulario de producto
 */
function enviarFormularioProducto(event) {
    event.preventDefault();

    if (!validarFormularioProducto()) {
        return false;
    }

    showLoadingModal('Guardando producto...');

    const formData = new FormData(event.target);

    fetch('productos.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(html => {
            hideLoadingModal();
            closeModal('addProductModal');

            // Recargar la página de productos después de 2 segundos
            setTimeout(() => {
                window.location.reload();
            }, 2000);

            // Mostrar mensaje de éxito
            showSuccessModal('Producto guardado correctamente');
        })
        .catch(error => {
            hideLoadingModal();
            console.error('Error:', error);
            showErrorModal('Error de conexión. Por favor intenta nuevamente.');
        });

    return false;
}

/**
 * Validar formato de código de barras
 */
function validarCodigoBarras(codigo) {
    // Validar que solo contenga números y tenga 13 dígitos
    const patron = /^\d{13}$/;
    return patron.test(codigo);
}

/**
 * Formatear código de barras automáticamente
 */
function formatearCodigoBarras(input) {
    let valor = input.value.replace(/\D/g, '');

    if (valor.length > 13) {
        valor = valor.substring(0, 13);
    }

    input.value = valor;
}

/**
 * Calcular ganancia en tiempo real
 */
function calcularGanancia() {
    const precioCompra = parseFloat(document.getElementById('precio_compra')?.value || 0);
    const precioVenta = parseFloat(document.getElementById('precio_venta')?.value || 0);

    if (precioCompra > 0 && precioVenta > 0) {
        const ganancia = precioVenta - precioCompra;
        const porcentajeGanancia = (ganancia / precioCompra) * 100;

        // Mostrar ganancia en consola para debugging
        console.log(`Ganancia: $${ganancia.toFixed(2)} (${porcentajeGanancia.toFixed(2)}%)`);
    }
}

/**
 * Calcular precio con impuesto
 */
function calcularPrecioConImpuesto() {
    const precioVenta = parseFloat(document.getElementById('precio_venta')?.value || 0);
    const impuesto = 7; // ITBMS fijo del 7% en Panamá

    if (precioVenta > 0) {
        const precioConImpuesto = precioVenta * (1 + impuesto / 100);

        // Mostrar precio con impuesto en consola para debugging
        console.log(`Precio con ITBMS: $${precioConImpuesto.toFixed(2)}`);
    }
}

// ===== EVENT LISTENERS PARA PRODUCTOS =====

// Inicializar formulario de producto cuando se carga
document.addEventListener('DOMContentLoaded', function () {
    const formProducto = document.getElementById('formProducto');
    if (formProducto) {
        formProducto.addEventListener('submit', enviarFormularioProducto);

        // Agregar listeners para cálculos automáticos
        const precioCompraInput = document.getElementById('precio_compra');
        if (precioCompraInput) {
            precioCompraInput.addEventListener('input', calcularPrecioVenta);
        }

        const precioVentaInput = document.getElementById('precio_venta');
        if (precioVentaInput) {
            precioVentaInput.addEventListener('input', calcularGanancia);
            precioVentaInput.addEventListener('input', calcularPrecioConImpuesto);
        }

        // Formateo de código de barras
        const codigoBarrasInput = document.getElementById('codigo_barras');
        if (codigoBarrasInput) {
            codigoBarrasInput.addEventListener('input', function () {
                formatearCodigoBarras(this);
            });
        }
    }
});

// ===== FUNCIONES ESPECÍFICAS PARA CLIENTES =====

/**
 * Alternar campos según tipo de cliente
 */
function toggleClienteFields() {
    const tipoCliente = document.getElementById('tipo_cliente').value;
    const nombreGrupo = document.getElementById('nombre_grupo');
    const razonSocialGrupo = document.getElementById('razon_social_grupo');
    const nombreInput = document.getElementById('nombre');
    const razonSocialInput = document.getElementById('razon_social');

    if (tipoCliente === 'natural') {
        nombreGrupo.style.display = 'block';
        razonSocialGrupo.style.display = 'none';
        nombreInput.required = true;
        razonSocialInput.required = false;
        nombreInput.focus();
    } else if (tipoCliente === 'juridico') {
        nombreGrupo.style.display = 'none';
        razonSocialGrupo.style.display = 'block';
        nombreInput.required = false;
        razonSocialInput.required = true;
        razonSocialInput.focus();
    } else {
        nombreGrupo.style.display = 'block';
        razonSocialGrupo.style.display = 'none';
        nombreInput.required = false;
        razonSocialInput.required = false;
    }
}

/**
 * Validar formulario de cliente
 */
function validarFormularioCliente() {
    const tipoCliente = document.getElementById('tipo_cliente').value;
    const cedulaRuc = document.getElementById('cedula_ruc').value;
    const telefono = document.getElementById('telefono').value;
    const email = document.getElementById('email').value;

    // Validar tipo de cliente
    if (!tipoCliente) {
        showErrorModal('Debe seleccionar un tipo de cliente');
        return false;
    }

    // Validar nombre o razón social según tipo
    if (tipoCliente === 'natural') {
        const nombre = document.getElementById('nombre').value;
        if (!nombre.trim()) {
            showErrorModal('El nombre es obligatorio para personas naturales');
            return false;
        }
    } else if (tipoCliente === 'juridico') {
        const razonSocial = document.getElementById('razon_social').value;
        if (!razonSocial.trim()) {
            showErrorModal('La razón social es obligatoria para personas jurídicas');
            return false;
        }
    }

    // Validar cédula/RUC
    if (!cedulaRuc.trim()) {
        showErrorModal('La cédula/RUC es obligatoria');
        return false;
    }

    // Validar formato de teléfono si se proporciona
    if (telefono && !validarTelefonoPanama(telefono)) {
        showErrorModal('El formato del teléfono no es válido. Use: 507-123-4567');
        return false;
    }

    // Validar formato de email si se proporciona
    if (email && !validarEmail(email)) {
        showErrorModal('El formato del email no es válido');
        return false;
    }

    return true;
}

/**
 * Enviar formulario de cliente
 */
function enviarFormularioCliente(event) {
    event.preventDefault();

    if (!validarFormularioCliente()) {
        return false;
    }

    showLoadingModal('Guardando cliente...');

    const formData = new FormData(event.target);

    fetch('clientes.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoadingModal();

            if (data.success) {
                showSuccessModal(data.message);
                closeModal('addClientModal');

                // Recargar la página de clientes después de 2 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showErrorModal(data.message || 'Error al guardar el cliente');
            }
        })
        .catch(error => {
            hideLoadingModal();
            console.error('Error:', error);
            showErrorModal('Error de conexión. Por favor intenta nuevamente.');
        });

    return false;
}

/**
 * Validar formato de teléfono panameño
 */
function validarTelefonoPanama(telefono) {
    const patron = /^507-\d{3}-\d{4}$/;
    return patron.test(telefono);
}

/**
 * Validar formato de email
 */
function validarEmail(email) {
    const patron = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return patron.test(email);
}

/**
 * Formatear teléfono automáticamente
 */
function formatearTelefono(input) {
    let valor = input.value.replace(/\D/g, '');

    if (valor.length >= 10) {
        valor = valor.substring(0, 10);
        input.value = valor.replace(/(\d{3})(\d{3})(\d{4})/, '507-$1-$2');
    }
}

/**
 * Formatear cédula/RUC automáticamente
 */
function formatearCedulaRuc(input) {
    let valor = input.value.replace(/\D/g, '');

    if (valor.length <= 7) {
        // Formato para persona natural: 8-123-456
        input.value = valor.replace(/(\d{1})(\d{3})(\d{3})/, '$1-$2-$3');
    } else if (valor.length <= 15) {
        // Formato para persona jurídica: 12345678-1-123456
        input.value = valor.replace(/(\d{8})(\d{1})(\d{6})/, '$1-$2-$3');
    }
}

// ===== EVENT LISTENERS =====

// Cerrar modales al hacer clic fuera de ellos
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
});

// Cerrar modales con la tecla Escape
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        const modalesAbiertos = document.querySelectorAll('.modal:not(.hidden)');
        modalesAbiertos.forEach(modal => {
            closeModal(modal.id);
        });
    }
});

// Inicializar formulario de cliente cuando se carga
document.addEventListener('DOMContentLoaded', function () {
    const formCliente = document.getElementById('formCliente');
    if (formCliente) {
        formCliente.addEventListener('submit', enviarFormularioCliente);

        // Agregar formateo automático
        const telefonoInput = document.getElementById('telefono');
        if (telefonoInput) {
            telefonoInput.addEventListener('input', function () {
                formatearTelefono(this);
            });
        }

        const cedulaRucInput = document.getElementById('cedula_ruc');
        if (cedulaRucInput) {
            cedulaRucInput.addEventListener('input', function () {
                formatearCedulaRuc(this);
            });
        }
    }
});

// ===== FUNCIONES ESPECÍFICAS PARA PROVEEDORES =====

/**
 * Validar formulario de proveedor
 */
function validarFormularioProveedor() {
    const nombre = document.getElementById('nombre').value;
    const ruc = document.getElementById('ruc').value;
    const telefonoPrincipal = document.getElementById('telefono_principal').value;
    const email = document.getElementById('email').value;
    const telefonoContacto = document.getElementById('telefono_contacto').value;
    const emailContacto = document.getElementById('email_contacto').value;
    const sitioWeb = document.getElementById('sitio_web').value;
    const diasCredito = parseFloat(document.getElementById('dias_credito').value);
    const descuentoPorcentaje = parseFloat(document.getElementById('descuento_porcentaje').value);
    const tiempoEntrega = parseFloat(document.getElementById('tiempo_entrega').value);
    const montoMinimo = parseFloat(document.getElementById('monto_minimo').value);

    // Validar nombre
    if (!nombre.trim()) {
        showErrorModal('El nombre del proveedor es obligatorio');
        return false;
    }

    // Validar RUC
    if (!ruc.trim()) {
        showErrorModal('El RUC es obligatorio');
        return false;
    }

    // Validar formato de teléfono principal si se proporciona
    if (telefonoPrincipal && !validarTelefonoPanama(telefonoPrincipal)) {
        showErrorModal('El formato del teléfono principal no es válido. Use: 507-123-4567');
        return false;
    }

    // Validar formato de email principal si se proporciona
    if (email && !validarEmail(email)) {
        showErrorModal('El formato del email principal no es válido');
        return false;
    }

    // Validar formato de teléfono de contacto si se proporciona
    if (telefonoContacto && !validarTelefonoPanama(telefonoContacto)) {
        showErrorModal('El formato del teléfono de contacto no es válido. Use: 507-123-4567');
        return false;
    }

    // Validar formato de email de contacto si se proporciona
    if (emailContacto && !validarEmail(emailContacto)) {
        showErrorModal('El formato del email de contacto no es válido');
        return false;
    }

    // Validar sitio web si se proporciona
    if (sitioWeb && !validarURL(sitioWeb)) {
        showErrorModal('El formato del sitio web no es válido');
        return false;
    }

    // Validar días de crédito
    if (diasCredito < 0) {
        showErrorModal('Los días de crédito no pueden ser negativos');
        return false;
    }

    // Validar descuento
    if (descuentoPorcentaje < 0 || descuentoPorcentaje > 100) {
        showErrorModal('El descuento debe estar entre 0% y 100%');
        return false;
    }

    // Validar tiempo de entrega
    if (tiempoEntrega < 0) {
        showErrorModal('El tiempo de entrega no puede ser negativo');
        return false;
    }

    // Validar monto mínimo
    if (montoMinimo < 0) {
        showErrorModal('El monto mínimo no puede ser negativo');
        return false;
    }

    return true;
}

/**
 * Enviar formulario de proveedor
 */
function enviarFormularioProveedor(event) {
    event.preventDefault();

    if (!validarFormularioProveedor()) {
        return false;
    }

    showLoadingModal('Guardando proveedor...');

    const formData = new FormData(event.target);

    fetch('guardar_proveedor.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoadingModal();

            if (data.success) {
                showSuccessModal(data.message);
                closeModal('addProviderModal');

                // Recargar la página de proveedores después de 2 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showErrorModal(data.message || 'Error al guardar el proveedor');
            }
        })
        .catch(error => {
            hideLoadingModal();
            console.error('Error:', error);
            showErrorModal('Error de conexión. Por favor intenta nuevamente.');
        });

    return false;
}

/**
 * Validar formato de URL
 */
function validarURL(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

/**
 * Formatear RUC automáticamente
 */
function formatearRuc(input) {
    let valor = input.value.replace(/\D/g, '');

    if (valor.length <= 15) {
        // Formato: 12345678-1-123456
        input.value = valor.replace(/(\d{8})(\d{1})(\d{6})/, '$1-$2-$3');
    }
}

// ===== EVENT LISTENERS PARA PROVEEDORES =====

// Inicializar formulario de proveedor cuando se carga
document.addEventListener('DOMContentLoaded', function () {
    const formProveedor = document.getElementById('formProveedor');
    if (formProveedor) {
        formProveedor.addEventListener('submit', enviarFormularioProveedor);

        // Agregar formateo automático para teléfonos
        const telefonoPrincipalInput = document.getElementById('telefono_principal');
        if (telefonoPrincipalInput) {
            telefonoPrincipalInput.addEventListener('input', function () {
                formatearTelefono(this);
            });
        }

        const telefonoContactoInput = document.getElementById('telefono_contacto');
        if (telefonoContactoInput) {
            telefonoContactoInput.addEventListener('input', function () {
                formatearTelefono(this);
            });
        }

        // Formateo de RUC
        const rucInput = document.getElementById('ruc');
        if (rucInput) {
            rucInput.addEventListener('input', function () {
                formatearRuc(this);
            });
        }
    }
});

// ===== FUNCIONES ESPECÍFICAS PARA COMPRAS =====

// Variables globales para compras
let productosCompra = [];
let filaProductoIndex = 0;

/**
 * Cargar proveedores en el select
 */
function cargarProveedores() {
    fetch('api/get_proveedores.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('proveedor_id');
                select.innerHTML = '<option value="">Seleccionar proveedor</option>';

                data.proveedores.forEach(proveedor => {
                    const option = document.createElement('option');
                    option.value = proveedor.id;
                    option.textContent = `${proveedor.codigo} - ${proveedor.nombre}`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error cargando proveedores:', error);
        });
}

/**
 * Cargar productos en el select
 */
function cargarProductos() {
    fetch('api/get_productos.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.productosDisponibles = data.productos;
            }
        })
        .catch(error => {
            console.error('Error cargando productos:', error);
        });
}

/**
 * Cargar información del proveedor seleccionado
 */
function cargarInfoProveedor() {
    const proveedorId = document.getElementById('proveedor_id').value;
    if (!proveedorId) return;

    fetch(`api/get_proveedor_info.php?id=${proveedorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Aquí puedes mostrar información adicional del proveedor
                console.log('Información del proveedor:', data.proveedor);
            }
        })
        .catch(error => {
            console.error('Error cargando información del proveedor:', error);
        });
}


/**
 * Seleccionar producto y cargar información
 */
function seleccionarProducto(filaIndex, productoId) {
    if (!productoId) return;

    const producto = window.productosDisponibles.find(p => p.id == productoId);
    if (!producto) return;

    const fila = document.getElementById(`fila-producto-${filaIndex}`);
    const precioInput = fila.querySelector('.precio-input');
    const cantidadInput = fila.querySelector('.cantidad-input');

    precioInput.value = producto.precio_compra || 0;
    cantidadInput.value = 1;

    calcularSubtotalFila(filaIndex);
}

/**
 * Calcular subtotal de una fila
 */
function calcularSubtotalFila(filaIndex) {
    const fila = document.getElementById(`fila-producto-${filaIndex}`);
    if (!fila) return;

    const cantidad = parseFloat(fila.querySelector('.cantidad-input').value) || 0;
    const precio = parseFloat(fila.querySelector('.precio-input').value) || 0;
    const descuento = parseFloat(fila.querySelector('.descuento-input').value) || 0;

    const subtotal = cantidad * precio;
    const descuentoMonto = subtotal * (descuento / 100);
    const subtotalConDescuento = subtotal - descuentoMonto;

    fila.querySelector('.subtotal-fila').textContent = `$${subtotalConDescuento.toFixed(2)}`;

    // Actualizar productos globales
    actualizarProductosCompra();
    calcularTotalesCompra();
}

/**
 * Eliminar fila de producto
 */
function eliminarFilaProducto(filaIndex) {
    const fila = document.getElementById(`fila-producto-${filaIndex}`);
    if (fila) {
        fila.remove();
        actualizarProductosCompra();
        calcularTotalesCompra();
    }
}

/**
 * Actualizar array de productos de la compra
 */
function actualizarProductosCompra() {
    productosCompra = [];
    const filas = document.querySelectorAll('#productosTableBody tr');

    filas.forEach((fila, index) => {
        const productoSelect = fila.querySelector('.producto-select');
        const cantidadInput = fila.querySelector('.cantidad-input');
        const precioInput = fila.querySelector('.precio-input');
        const descuentoInput = fila.querySelector('.descuento-input');

        if (productoSelect.value) {
            const producto = window.productosDisponibles.find(p => p.id == productoSelect.value);
            productosCompra.push({
                producto_id: parseInt(productoSelect.value),
                producto_nombre: producto.nombre,
                cantidad: parseFloat(cantidadInput.value) || 0,
                precio_unitario: parseFloat(precioInput.value) || 0,
                descuento_porcentaje: parseFloat(descuentoInput.value) || 0,
                impuesto_porcentaje: producto.impuesto || 0
            });
        }
    });
}

/**
 * Calcular totales de la compra
 */
function calcularTotalesCompra() {
    let subtotal = 0;
    let totalDescuentos = 0;
    let totalImpuestos = 0;

    productosCompra.forEach(producto => {
        const subtotalProducto = producto.cantidad * producto.precio_unitario;
        const descuentoProducto = subtotalProducto * (producto.descuento_porcentaje / 100);
        const subtotalConDescuento = subtotalProducto - descuentoProducto;
        const impuestoProducto = subtotalConDescuento * (producto.impuesto_porcentaje / 100);

        subtotal += subtotalProducto;
        totalDescuentos += descuentoProducto;
        totalImpuestos += impuestoProducto;
    });

    const total = subtotal - totalDescuentos + totalImpuestos;

    // Actualizar elementos en el DOM
    document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('total_descuentos').textContent = `$${totalDescuentos.toFixed(2)}`;
    document.getElementById('total_impuestos').textContent = `$${totalImpuestos.toFixed(2)}`;
    document.getElementById('total_compra').textContent = `$${total.toFixed(2)}`;
}

/**
 * Validar formulario de compra
 */
function validarFormularioCompra() {
    const fechaCompra = document.getElementById('fecha_compra').value;
    const proveedorId = document.getElementById('proveedor_id').value;

    if (!fechaCompra) {
        showErrorModal('La fecha de compra es obligatoria');
        return false;
    }

    if (!proveedorId) {
        showErrorModal('Debe seleccionar un proveedor');
        return false;
    }

    if (productosCompra.length === 0) {
        showErrorModal('Debe agregar al menos un producto a la compra');
        return false;
    }

    // Validar que todos los productos tengan cantidad y precio válidos
    for (let producto of productosCompra) {
        if (producto.cantidad <= 0) {
            showErrorModal('La cantidad debe ser mayor a cero para todos los productos');
            return false;
        }

        if (producto.precio_unitario < 0) {
            showErrorModal('El precio unitario no puede ser negativo');
            return false;
        }
    }

    return true;
}

/**
 * Enviar formulario de compra
 */
function enviarFormularioCompra(event) {
    event.preventDefault();

    if (!validarFormularioCompra()) {
        return false;
    }

    showLoadingModal('Guardando compra...');

    const formData = new FormData(event.target);
    formData.append('productos', JSON.stringify(productosCompra));

    fetch('compra.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            hideLoadingModal();

            if (data.success) {
                showSuccessModal(data.message);
                closeModal('addPurchaseModal');

                // Recargar la página de compras después de 2 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showErrorModal(data.message || 'Error al guardar la compra');
            }
        })
        .catch(error => {
            hideLoadingModal();
            console.error('Error:', error);
            showErrorModal('Error de conexión. Por favor intenta nuevamente.');
        });

    return false;
}

/**
 * Ver detalle de compra
 */
function verDetalleCompra(compraId) {
    fetch(`api/get_compra_detalle.php?id=${compraId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarDetalleCompra(data.compra);
                openModal('purchaseDetailModal');
            } else {
                showErrorModal(data.message || 'Error al cargar el detalle de la compra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorModal('Error de conexión. Por favor intenta nuevamente.');
        });
}

/**
 * Mostrar detalle de compra en el modal
 */
function mostrarDetalleCompra(compra) {
    const content = document.getElementById('purchaseDetailContent');

    content.innerHTML = `
        <div class="purchase-detail">
            <div class="detail-header">
                <div class="detail-info">
                    <h3>Compra ${compra.codigo}</h3>
                    <p><strong>Fecha:</strong> ${new Date(compra.fecha_compra).toLocaleString()}</p>
                    <p><strong>Proveedor:</strong> ${compra.proveedor_nombre}</p>
                    <p><strong>Estado:</strong> <span class="status-badge status-${compra.estado}">${compra.estado}</span></p>
                </div>
                <div class="detail-totals">
                    <p><strong>Subtotal:</strong> $${parseFloat(compra.subtotal).toFixed(2)}</p>
                    <p><strong>Descuentos:</strong> $${parseFloat(compra.total_descuentos).toFixed(2)}</p>
                    <p><strong>Impuestos:</strong> $${parseFloat(compra.total_impuestos).toFixed(2)}</p>
                    <p><strong>Total:</strong> $${parseFloat(compra.total).toFixed(2)}</p>
                </div>
            </div>
            
            <div class="detail-products">
                <h4>Productos</h4>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Descuento</th>
                            <th>Impuesto</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${compra.productos.map(producto => `
                            <tr>
                                <td>${producto.producto_nombre}</td>
                                <td>${producto.cantidad}</td>
                                <td>$${parseFloat(producto.precio_unitario).toFixed(2)}</td>
                                <td>$${parseFloat(producto.descuento_monto).toFixed(2)}</td>
                                <td>$${parseFloat(producto.impuesto_monto).toFixed(2)}</td>
                                <td>$${parseFloat(producto.total).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            
            ${compra.observaciones ? `
                <div class="detail-observations">
                    <h4>Observaciones</h4>
                    <p>${compra.observaciones}</p>
                </div>
            ` : ''}
        </div>
    `;
}

/**
 * Editar compra
 */
function editarCompra(compraId) {
    // Implementar edición de compra
    showErrorModal('Función de edición en desarrollo');
}

/**
 * Recibir compra (marcar como recibida)
 */
function recibirCompra(compraId) {
    if (confirm('¿Está seguro de marcar esta compra como recibida? Esto actualizará el inventario.')) {
        fetch('api/recibir_compra.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ compra_id: compraId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessModal('Compra marcada como recibida exitosamente');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showErrorModal(data.message || 'Error al recibir la compra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('Error de conexión. Por favor intenta nuevamente.');
            });
    }
}

/**
 * Imprimir compra
 */
function imprimirCompra() {
    // Implementar impresión de compra
    showErrorModal('Función de impresión en desarrollo');
}

// ===== EVENT LISTENERS PARA COMPRAS =====

// Inicializar sistema de compras cuando se carga
document.addEventListener('DOMContentLoaded', function () {
    const formCompra = document.getElementById('formCompra');
    if (formCompra) {
        formCompra.addEventListener('submit', enviarFormularioCompra);

        // Cargar datos necesarios
        cargarProveedores();
        cargarProductos();

        // Agregar primera fila de producto
        agregarFilaProducto();
    }
});

// ===== FUNCIONES ESPECÍFICAS PARA PRODUCTOS =====
async function showEditProductModal(productId) {
    showLoadingModal('Cargando datos del producto...');

    try {
        const response = await fetch(`api/get_producto_detalle.php?id=${productId}`);
        if (!response.ok) throw new Error('Error en la respuesta de la API');

        const result = await response.json();

        if (result.success) {
            const producto = result.data;
            const modalId = 'editProductModal';
            const modal = document.getElementById(modalId);
            const form = document.getElementById('formEditProducto');

            if (!modal || !form) {
                throw new Error('Modal de edición de producto no encontrado');
            }

            // Rellenar formulario con los datos del producto
            form.querySelector('#edit_producto_id').value = producto.id;
            form.querySelector('#edit_codigo').value = producto.codigo || '';
            form.querySelector('#edit_codigo_barras').value = producto.codigo_barras || '';
            form.querySelector('#edit_nombre').value = producto.nombre || '';
            form.querySelector('#edit_descripcion').value = producto.descripcion || '';
            form.querySelector('#edit_categoria_id').value = producto.categoria_id || '';
            form.querySelector('#edit_marca_id').value = producto.marca_id || '';
            form.querySelector('#edit_precio_compra').value = parseFloat(producto.precio_compra || 0).toFixed(2);
            form.querySelector('#edit_precio_venta').value = parseFloat(producto.precio_venta || 0).toFixed(2);
            form.querySelector('#edit_stock_minimo').value = producto.stock_minimo || '0';
            form.querySelector('#edit_stock_actual').value = producto.stock_actual || '0';

            hideLoadingModal();
            openModal(modalId);
        } else {
            throw new Error(result.message || 'No se pudo cargar el producto.');
        }
    } catch (error) {
        console.error('Error al editar producto:', error);
        hideLoadingModal();
        showErrorModal(error.message);
    }
}

async function deleteProduct(productId, productName) {
    showConfirmationModal(
        'Eliminar Producto',
        `¿Estás seguro de que quieres eliminar '${productName}'? Esta acción no se puede deshacer y no funcionará si el producto tiene ventas asociadas.`,
        async () => {
            showLoadingModal('Eliminando producto...');
            try {
                const response = await fetch('productos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: productId })
                });

                const result = await response.json();
                hideLoadingModal();

                if (result.success) {
                    showSuccessModal(result.message);
                    // Recargar la vista de productos para reflejar los cambios
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showErrorModal(result.message);
                }
            } catch (error) {
                hideLoadingModal();
                showErrorModal('Error de conexión al intentar eliminar el producto.');
                console.error('Error:', error);
            }
        }
    );
}

// ===== FUNCIONES ADICIONALES PARA PRODUCTOS =====

/**
 * Mostrar modal para ajustar stock
 */
function showAdjustStockModal(productId, productName, currentStock) {
    const modalContent = `
        <div class="modal-header">
            <h2><i class="fas fa-warehouse"></i> Ajustar Stock</h2>
            <button class="modal-close" onclick="closeModal('adjustStockModal')"><i class="fas fa-times"></i></button>
        </div>
        <form class="modal-form" id="adjustStockForm" onsubmit="enviarAjusteStock(event)">
            <input type="hidden" name="action" value="ajustar_stock">
            <input type="hidden" name="producto_id" value="${productId}">
            
            <div class="form-group">
                <label>Producto</label>
                <input type="text" value="${productName}" readonly class="form-control">
            </div>
            
            <div class="form-group">
                <label>Stock Actual</label>
                <input type="number" value="${currentStock}" readonly class="form-control">
            </div>
            
            <div class="form-group">
                <label for="nuevo_stock">Nuevo Stock</label>
                <input type="number" id="nuevo_stock" name="nuevo_stock" min="0" value="${currentStock}" required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="motivo">Motivo del Ajuste</label>
                <textarea id="motivo" name="motivo" rows="3" required class="form-control" placeholder="Ej: Inventario físico, pérdida, etc."></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('adjustStockModal')">Cancelar</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar Ajuste</button>
            </div>
        </form>
    `;

    // Crear modal dinámicamente si no existe
    let modal = document.getElementById('adjustStockModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'adjustStockModal';
        modal.className = 'modal hidden';
        modal.innerHTML = `
            <div class="modal-content">
                ${modalContent}
            </div>
        `;
        document.body.appendChild(modal);
    } else {
        modal.querySelector('.modal-content').innerHTML = modalContent;
    }

    openModal('adjustStockModal');
}

/**
 * Enviar ajuste de stock
 */
function enviarAjusteStock(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const nuevoStock = parseInt(formData.get('nuevo_stock'));
    const motivo = formData.get('motivo');

    if (!motivo.trim()) {
        showErrorModal('Debe especificar un motivo para el ajuste de stock');
        return false;
    }

    showLoadingModal('Guardando ajuste de stock...');

    fetch('productos.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(html => {
            hideLoadingModal();
            closeModal('adjustStockModal');

            // Recargar la página de productos
            setTimeout(() => {
                window.location.reload();
            }, 2000);

            // Mostrar mensaje de éxito
            showSuccessModal('Stock ajustado correctamente');
        })
        .catch(error => {
            hideLoadingModal();
            console.error('Error:', error);
            showErrorModal('Error de conexión. Por favor intenta nuevamente.');
        });

    return false;
}



// ===== FUNCIONES PARA CLIENTES =====

/**
 * Editar cliente
 */
function editarCliente(clienteId) {
    // Redirigir a la página de edición de cliente
    window.location.href = `clientes.php?action=editar&id=${clienteId}`;
}

/**
 * Ver historial de cliente
 */
function verHistorialCliente(clienteId) {
    // Abrir modal con historial
    fetch(`clientes.php?action=ver_historial_cliente&id=ID`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarHistorialCliente(data.historial);
                openModal('clientHistoryModal');
            } else {
                showErrorModal(data.message || 'Error al cargar el historial');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorModal('Error de conexión al cargar el historial');
        });
}

/**
 * Mostrar historial de cliente en modal
 */
function mostrarHistorialCliente(historial) {
    const content = document.getElementById('clientHistoryContent');

    if (!content) {
        console.error('Elemento clientHistoryContent no encontrado');
        return;
    }

    if (!historial || historial.length === 0) {
        content.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-search fa-3x text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No hay historial de compras para este cliente.</p>
                <p class="text-gray-400 text-sm mt-2">El cliente aún no ha realizado ninguna compra.</p>
            </div>
        `;
        return;
    }

    let html = `
        <div class="historial-table">
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Factura</th>
                        <th>Tipo Pago</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
    `;

    historial.forEach(compra => {
        // Formatear fecha
        const fecha = new Date(compra.fecha);
        const fechaFormateada = fecha.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        // Formatear tipo de pago
        const tipoPago = compra.tipo_pago || 'N/A';
        const tipoPagoFormateado = tipoPago.charAt(0).toUpperCase() + tipoPago.slice(1);

        // Formatear estado
        const estado = compra.estado || 'pendiente';
        const estadoFormateado = estado.charAt(0).toUpperCase() + estado.slice(1);

        // Clase CSS para el estado
        let estadoClass = 'status-badge ';
        switch (estado) {
            case 'pagada':
                estadoClass += 'status-success';
                break;
            case 'pendiente':
                estadoClass += 'status-warning';
                break;
            case 'anulada':
                estadoClass += 'status-danger';
                break;
            case 'vencida':
                estadoClass += 'status-danger';
                break;
            default:
                estadoClass += 'status-secondary';
        }

        html += `
            <tr>
                <td>${fechaFormateada}</td>
                <td><strong>${compra.numero_factura}</strong></td>
                <td>${tipoPagoFormateado}</td>
                <td><strong>$${parseFloat(compra.total || 0).toFixed(2)}</strong></td>
                <td><span class="${estadoClass}">${estadoFormateado}</span></td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Total de facturas: <strong>${historial.length}</strong></p>
                    <p class="text-sm text-gray-600">Total facturado: <strong>$${historial.reduce((sum, compra) => sum + parseFloat(compra.total || 0), 0).toFixed(2)}</strong></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Mostrando las últimas 20 facturas</p>
                </div>
            </div>
        </div>
    `;

    content.innerHTML = html;
}

/**
 * Eliminar/desactivar cliente
 */
function eliminarCliente(clienteId, nombreCliente) {
    showConfirmationModal(
        'Desactivar Cliente',
        `¿Estás seguro de que quieres desactivar al cliente '${nombreCliente}'? Esta acción no se puede deshacer.`,
        () => {
            window.location.href = `clientes.php?action=eliminar&id=${clienteId}`;
        }
    );
}

// ===== FUNCIONES PARA INVENTARIO =====

/**
 * Editar producto desde inventario
 */
function editarProducto(productoId) {
    // Redirigir a la página de edición de producto
    window.location.href = `productos.php?action=editar&id=${productoId}`;
}

/**
 * Eliminar/desactivar producto desde inventario
 */
function eliminarProducto(productoId, nombreProducto) {
    showConfirmationModal(
        'Desactivar Producto',
        `¿Estás seguro de que quieres desactivar el producto '${nombreProducto}'? Esta acción no se puede deshacer.`,
        () => {
            window.location.href = `productos.php?action=eliminar&id=${productoId}`;
        }
    );
}

// ===== FUNCIONES PARA VENTAS =====

/**
 * Ver factura
 */
function verFactura(ventaId) {
    // Redirigir a la página de detalle de factura
    window.location.href = `ventas.php?action=ver&id=${ventaId}`;
}

/**
 * Abrir modal de pago
 */
function abrirModalPago(ventaId, total) {
    // Implementar modal de pago
    showErrorModal('Función de pago en desarrollo');
}

/**
 * Anular factura
 */
function anularFactura(ventaId, numeroFactura) {
    showConfirmationModal(
        'Anular Factura',
        `¿Estás seguro de que quieres anular la factura '${numeroFactura}'? Esta acción no se puede deshacer.`,
        () => {
            window.location.href = `ventas.php?action=anular&id=${ventaId}`;
        }
    );
}

/**
 * Imprimir factura
 */
function imprimirFactura(ventaId) {
    // Abrir ventana de impresión
    window.open(`ventas.php?action=imprimir&id=${ventaId}`, '_blank');
}

// ===== FUNCIONES ADICIONALES PARA MODALES =====

/**
 * Alternar campos de cliente en formulario de edición
 */
function toggleEditClienteFields() {
    const tipoCliente = document.getElementById('edit_tipo_cliente').value;
    const nombreGrupo = document.getElementById('edit_nombre_grupo');
    const razonSocialGrupo = document.getElementById('edit_razon_social_grupo');
    const nombreInput = document.getElementById('edit_nombre');
    const razonSocialInput = document.getElementById('edit_razon_social');

    if (tipoCliente === 'natural') {
        nombreGrupo.style.display = 'block';
        razonSocialGrupo.style.display = 'none';
        nombreInput.required = true;
        razonSocialInput.required = false;
    } else if (tipoCliente === 'juridico') {
        nombreGrupo.style.display = 'none';
        razonSocialGrupo.style.display = 'block';
        nombreInput.required = false;
        razonSocialInput.required = true;
    } else {
        nombreGrupo.style.display = 'block';
        razonSocialGrupo.style.display = 'none';
        nombreInput.required = false;
        razonSocialInput.required = false;
    }
}

/**
 * Enviar formulario de edición de producto
 */
function enviarFormularioEditProducto(event) {
    event.preventDefault();

    if (!validarFormularioEditProducto()) {
        return false;
    }

    showLoadingModal('Actualizando producto...');

    const formData = new FormData(event.target);

    fetch('productos.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(html => {
            hideLoadingModal();
            closeModal('editProductModal');

            // Recargar la página de productos después de 2 segundos
            setTimeout(() => {
                window.location.reload();
            }, 2000);

            // Mostrar mensaje de éxito
            showSuccessModal('Producto actualizado correctamente');
        })
        .catch(error => {
            hideLoadingModal();
            console.error('Error:', error);
            showErrorModal('Error de conexión. Por favor intenta nuevamente.');
        });

    return false;
}

/**
 * Enviar formulario de ajuste de stock
 */
function enviarFormularioAdjustStock(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const nuevoStock = parseInt(formData.get('nuevo_stock'));
    const motivo = formData.get('motivo');

    if (!motivo.trim()) {
        showErrorModal('Debe especificar un motivo para el ajuste de stock');
        return false;
    }

    showLoadingModal('Guardando ajuste de stock...');

    fetch('productos.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(html => {
            hideLoadingModal();
            closeModal('adjustStockModal');

            // Recargar la página de productos
            setTimeout(() => {
                window.location.reload();
            }, 2000);

            // Mostrar mensaje de éxito
            showSuccessModal('Stock ajustado correctamente');
        })
        .catch(error => {
            hideLoadingModal();
            console.error('Error:', error);
            showErrorModal('Error de conexión. Por favor intenta nuevamente.');
        });

    return false;
}

/**
 * Validar formulario de edición de producto
 */
function validarFormularioEditProducto() {
    const codigo = document.getElementById('edit_codigo')?.value;
    const nombre = document.getElementById('edit_nombre')?.value;
    const precioVenta = parseFloat(document.getElementById('edit_precio_venta')?.value || 0);
    const codigoBarras = document.getElementById('edit_codigo_barras')?.value;
    const precioCompra = parseFloat(document.getElementById('edit_precio_compra')?.value || 0);
    const stockMinimo = parseFloat(document.getElementById('edit_stock_minimo')?.value || 0);

    // Validar código
    if (!codigo || !codigo.trim()) {
        showErrorModal('El código del producto es obligatorio');
        return false;
    }

    // Validar nombre
    if (!nombre || !nombre.trim()) {
        showErrorModal('El nombre del producto es obligatorio');
        return false;
    }

    // Validar precio de venta
    if (precioVenta <= 0) {
        showErrorModal('El precio de venta debe ser mayor a cero');
        return false;
    }

    // Validar código de barras si se proporciona
    if (codigoBarras && !validarCodigoBarras(codigoBarras)) {
        showErrorModal('El código de barras debe tener 13 dígitos numéricos');
        return false;
    }

    // Validar precio de compra
    if (precioCompra < 0) {
        showErrorModal('El precio de compra no puede ser negativo');
        return false;
    }

    // Validar stock mínimo
    if (stockMinimo < 0) {
        showErrorModal('El stock mínimo no puede ser negativo');
        return false;
    }

    return true;
} 