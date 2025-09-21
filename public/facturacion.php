<?php
require_once '../config/connection.php';
include 'partials/head.php';
?>

<body>
    <div id="dashboardScreen" class="dashboard-container">
        <?php include 'partials/sidebar.php'; ?>
        <main class="main-content">
            <?php include 'partials/header.php'; ?>
            <div class="content-area">
                <div class="page-header">
                   
<h2>Buscar Cliente</h2>

<div style="position: relative;">
    <input type="text" id="cliente_busqueda" placeholder="Nombre o cédula/RUC" autocomplete="off" style="width:300px;">
    <div id="cliente-resultados" class="search-results"></div>
</div>

<div id="customer-card" class="customer-card">
    <p><strong>Nombre:</strong> <span id="customer-name"></span></p>
    <p><strong>Cédula/RUC:</strong> <span id="customer-id"></span></p>
    <p><strong>Teléfono:</strong> <span id="customer-phone"></span></p>
    <p><strong>Email:</strong> <span id="customer-email"></span></p>
    <p><strong>Dirección:</strong> <span id="customer-address"></span></p>
    <input type="hidden" id="cliente_id" name="cliente_id">
</div>


<input type="text" id="producto" onkeyup="buscarProducto()" placeholder="Escriba el nombre o código del producto">
    <div id="resultado"></div>


<script>
const input = document.getElementById('cliente_busqueda');
const resultadosDiv = document.getElementById('cliente-resultados');
const customerCard = document.getElementById('customer-card');

input.addEventListener('input', function() {
    const query = this.value.trim();
    if(query.length < 2){
        resultadosDiv.style.display = 'none';
        return;
    }

    fetch(`api/buscar_clientes.php?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            resultadosDiv.innerHTML = '';
            if(data.success && data.clientes.length > 0){
                data.clientes.forEach(cliente => {
                    const div = document.createElement('div');
                    div.textContent = `${cliente.nombre} - ${cliente.cedula_ruc}`;
                    div.addEventListener('click', () => {
                        // Al hacer clic, obtener detalles completos del cliente
                        fetch(`api/get_cliente_detalle.php?id=${cliente.id}`)
                            .then(res => res.json())
                            .then(detalle => {
                                if(detalle.success){
                                    document.getElementById('customer-name').textContent = detalle.cliente.nombre;
                                    document.getElementById('customer-id').textContent = detalle.cliente.cedula_ruc;
                                    document.getElementById('customer-phone').textContent = detalle.cliente.telefono;
                                    document.getElementById('customer-email').textContent = detalle.cliente.email;
                                    document.getElementById('customer-address').textContent = detalle.cliente.direccion;
                                    document.getElementById('cliente_id').value = detalle.cliente.id;
                                    customerCard.style.display = 'block';
                                    resultadosDiv.style.display = 'none';
                                    input.value = detalle.cliente.nombre;
                                } else {
                                    alert(detalle.error);
                                }
                            });
                    });
                    resultadosDiv.appendChild(div);
                });
                resultadosDiv.style.display = 'block';
            } else {
                resultadosDiv.style.display = 'none';
            }
        })
        .catch(err => console.error(err));
});



async function buscarProducto() {
            const query = document.getElementById('producto').value;

            if (query.length < 2) {
                document.getElementById('resultado').innerHTML = '';
                return;
            }

            try {
                const response = await fetch('api/get_producto_detalle.php?q=' + encodeURIComponent(query));
                const data = await response.json();

                let html = '<ul>';
                if (data.success && data.productos.length > 0) {
                    data.productos.forEach(p => {
                        html += `<li>
                            <strong>${p.nombre}</strong> (Código: ${p.codigo})<br>
                            Precio: $${p.precio} | Stock: ${p.stock}
                        </li>`;
                    });
                } else {
                    html = '<li>No se encontraron productos</li>';
                }
                html += '</ul>';

                document.getElementById('resultado').innerHTML = html;
            } catch (error) {
                document.getElementById('resultado').innerHTML = '<p style="color:red;">Error al buscar</p>';
            }
        }
        
</script>

                </div>
                <!-- Aquí va el contenido de compras -->
            </div>
        </main>
    </div>
    <?php include 'partials/modals.php'; ?>
</body>

</html>