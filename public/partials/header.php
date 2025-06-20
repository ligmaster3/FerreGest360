<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FerreGest360 - Sistema de Gestión para Ferreterías</title>
    <link rel="stylesheet" href="/public/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<!-- Header -->
<header class="header">
    <div class="header-left">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar productos, clientes..." class="search-input">
        </div>
    </div>

    <div class="header-right">
        <button class="notification-btn">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
        </button>
        <div class="current-date">
            <span id="currentDate"></span>
        </div>
    </div>
</header>