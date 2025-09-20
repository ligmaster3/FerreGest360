<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-tools"></i>
        <span class="sidebar-title">FerreGest360</span>
    </div>

    <div class="user-info">
        <div class="user-avatar" title="usuario">
            <i title="usuario" class="fas fa-user"></i>
        </div>
        <div class="user-details">
            <span class="user-name">Admin Sistema</span>
            <span class="user-role">Administrador</span>
        </div>
    </div>

    <ul class="nav-menu">
        <li class="nav-item" data-section="dashboard">
            <a href="dashboard.php" class="nav-link" data-target="dashboard.php">
                <i class="fas fa-home" title="dashboard"></i>
                <span>Panel Principal</span>
            </a>
        </li>
        <li class="nav-item" data-section="productos">
            <a href="productos.php" class="nav-link" data-target="productos.php">
                <i class="fas fa-tools" title="Productos"></i>
                <span>Productos</span>
            </a>
        </li>
        <li class="nav-item" data-section="inventario">
            <a href="inventario.php" class="nav-link" data-target="inventario.php">
                <i class="fas fa-boxes" title="Inventario"></i>
                <span>Inventario</span>
            </a>
        </li>
        <li class="nav-item" data-section="ventas">
            <a href="ventas.php" class="nav-link" data-target="ventas.php">
                <i class="fas fa-shopping-cart" title="Ventas"></i>
                <span>Ventas</span>
            </a>
        </li>
        <li class="nav-item" data-section="clientes">
            <a href="clientes.php" class="nav-link" data-target="clientes.php">
                <i class="fas fa-users" title="Clientes"></i>
                <span>Clientes</span>
            </a>
        </li>
        <li class="nav-item" data-section="proveedores">
            <a href="proveedores.php" class="nav-link" data-target="proveedores.php">
                <i class="fas fa-truck" title="Proveedores"></i>
                <span>Proveedores</span>
            </a>
        </li>
        <li class="nav-item" data-section="reportes">
            <!-- <a href="reportes.php" class="nav-link" data-target="reportes.php">
                <i class="fas fa-chart-bar" title="Reportes"></i>
                <span>Reportes</span>
            </a> -->
        </li>

        <div class="sidebar-footer">
            <button class="logout-btn" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </button>
        </div>
    </ul>
</nav>