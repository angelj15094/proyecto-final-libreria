<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Librería Horizonte';
$pageDescription = $pageDescription ?? 'Catálogo de libros y autores de Librería Horizonte.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#17352f">
    <title><?= e($pageTitle) ?> | Librería Horizonte</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
    <a class="skip-link" href="#contenido-principal">Saltar al contenido</a>
    <header class="site-header">
        <div class="container header__inner">
            <a class="brand" href="index.php" aria-label="Librería Horizonte, inicio">
                <span class="brand__mark" aria-hidden="true">LH</span>
                <span>
                    <strong>Librería Horizonte</strong>
                    <small>Historias para cada camino</small>
                </span>
            </a>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="navegacion-principal">
                <span class="nav-toggle__label">Menú</span>
                <span class="nav-toggle__icon" aria-hidden="true"></span>
            </button>

            <nav class="nav" id="navegacion-principal" aria-label="Navegación principal">
                <a class="<?= nav_class('index.php') ?>" href="index.php">Inicio</a>
                <a class="<?= nav_class('libros.php') ?>" href="libros.php">Libros</a>
                <a class="<?= nav_class('autores.php') ?>" href="autores.php">Autores</a>
                <a class="<?= nav_class('contacto.php') ?>" href="contacto.php">Contacto</a>
            </nav>
        </div>
    </header>
    <main id="contenido-principal">
