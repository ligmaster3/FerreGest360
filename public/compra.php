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
                    <div class="">
                        <h1>Compra</h1>
                    </div>
                </div>
                <!-- Aquí va el contenido de compras -->
            </div>
        </main>
    </div>
    <?php include 'partials/modals.php'; ?>
</body>

</html>