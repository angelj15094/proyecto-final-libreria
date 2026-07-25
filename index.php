<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Inicio';
$pageDescription = 'Descubre el catálogo de libros y autores disponibles en Librería Horizonte.';
$pdo = safe_db();
$stats = [
    'libros' => 0,
    'autores' => 0,
    'categorias' => 0,
];
$destacados = [];

if ($pdo) {
    // PDO query() se utiliza en consultas fijas, sin datos suministrados por el usuario.
    $stats['libros'] = (int) $pdo->query('SELECT COUNT(*) FROM titulos')->fetchColumn();
    $stats['autores'] = (int) $pdo->query('SELECT COUNT(*) FROM autores')->fetchColumn();
    $stats['categorias'] = (int) $pdo->query('SELECT COUNT(DISTINCT tipo) FROM titulos')->fetchColumn();

    $destacados = $pdo->query(
        "SELECT
            t.id_titulo,
            t.titulo,
            t.tipo,
            t.precio,
            t.notas,
            COALESCE(
                GROUP_CONCAT(
                    CONCAT(TRIM(a.nombre), ' ', TRIM(a.apellido))
                    ORDER BY CAST(ta.ord_au AS UNSIGNED)
                    SEPARATOR ', '
                ),
                'Autor no disponible'
            ) AS autores
        FROM titulos t
        LEFT JOIN titulo_autor ta ON ta.id_titulo = t.id_titulo
        LEFT JOIN autores a ON a.id_autor = ta.id_autor
        WHERE t.contrato = '1'
        GROUP BY t.id_titulo, t.titulo, t.tipo, t.precio, t.notas, t.total_ventas
        ORDER BY t.total_ventas DESC, t.titulo ASC
        LIMIT 3"
    )->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero__grid">
        <div class="hero__content" data-reveal>
            <p class="eyebrow">Tu próxima lectura empieza aquí</p>
            <h1>Libros que abren nuevos horizontes.</h1>
            <p class="hero__lead">
                Explora una colección seleccionada de negocios, computación, psicología y cocina.
                Conoce las voces detrás de cada título.
            </p>
            <form class="hero-search" action="libros.php" method="get" role="search">
                <label class="sr-only" for="busqueda-inicio">Buscar en el catálogo</label>
                <input id="busqueda-inicio" name="q" type="search" placeholder="Título, autor o palabra clave">
                <button class="button button--primary" type="submit">Buscar libros</button>
            </form>
            <div class="hero__actions">
                <a class="text-link" href="autores.php">Conocer a los autores <span aria-hidden="true">→</span></a>
            </div>
        </div>

        <div class="hero__visual" aria-label="Selección de libros de Librería Horizonte" data-reveal>
            <div class="book-stack" aria-hidden="true">
                <div class="book book--one"><span>Ideas</span></div>
                <div class="book book--two"><span>Sabores</span></div>
                <div class="book book--three"><span>Mentes</span></div>
            </div>
            <div class="quote-card">
                <span class="quote-card__mark" aria-hidden="true">“</span>
                <p>Cada libro es una conversación que atraviesa el tiempo.</p>
                <small>Catálogo Horizonte</small>
            </div>
        </div>
    </div>
</section>

<?php if (!$pdo): ?>
    <section class="container status-panel status-panel--warning" role="status">
        <div>
            <strong>El catálogo está temporalmente fuera de servicio.</strong>
            <p>Estamos intentando restablecer la conexión con la biblioteca.</p>
        </div>
    </section>
<?php endif; ?>

<section class="stats" aria-label="Resumen del catálogo">
    <div class="container stats__grid">
        <article>
            <strong><?= number_format($stats['libros']) ?></strong>
            <span>libros disponibles</span>
        </article>
        <article>
            <strong><?= number_format($stats['autores']) ?></strong>
            <span>autores en catálogo</span>
        </article>
        <article>
            <strong><?= number_format($stats['categorias']) ?></strong>
            <span>categorías para explorar</span>
        </article>
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        <div class="section-heading" data-reveal>
            <div>
                <p class="eyebrow">Los más leídos</p>
                <h2>Títulos destacados</h2>
            </div>
            <a class="text-link" href="libros.php">Ver catálogo completo <span aria-hidden="true">→</span></a>
        </div>

        <?php if ($destacados): ?>
            <div class="featured-grid">
                <?php foreach ($destacados as $index => $libro): ?>
                    <article class="featured-card" data-reveal>
                        <div class="mini-cover mini-cover--<?= e($libro['tipo']) ?>" aria-hidden="true">
                            <span><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                            <strong>Horizonte</strong>
                        </div>
                        <div class="featured-card__body">
                            <span class="pill"><?= e(format_book_type($libro['tipo'])) ?></span>
                            <h3><?= e($libro['titulo']) ?></h3>
                            <p class="byline">Por <?= e($libro['autores']) ?></p>
                            <p><?= e(truncate_text($libro['notas'], 125)) ?></p>
                            <div class="featured-card__meta">
                                <strong><?= e(format_price($libro['precio'])) ?></strong>
                                <a href="libros.php?q=<?= urlencode($libro['id_titulo']) ?>">Ver libro</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span aria-hidden="true">⌁</span>
                <h3>Aún no hay títulos para mostrar</h3>
                <p>El catálogo aparecerá aquí cuando la base de datos esté disponible.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container discovery-grid">
        <div class="discovery-copy" data-reveal>
            <p class="eyebrow">Explora a tu manera</p>
            <h2>Una colección, muchas puertas de entrada.</h2>
            <p>
                Busca por título, filtra por categoría o conoce el recorrido de cada autor.
                El catálogo fue diseñado para ayudarte a llegar rápido a la lectura indicada.
            </p>
            <a class="button button--secondary" href="libros.php">Explorar el catálogo</a>
        </div>
        <div class="category-list" data-reveal>
            <a href="libros.php?tipo=business"><span>01</span><strong>Negocios</strong><em>Ideas para decidir y crecer</em></a>
            <a href="libros.php?tipo=popular_comp"><span>02</span><strong>Computación</strong><em>Tecnología explicada con claridad</em></a>
            <a href="libros.php?tipo=psychology"><span>03</span><strong>Psicología</strong><em>Comprender emociones y conductas</em></a>
            <a href="libros.php?tipo=trad_cook"><span>04</span><strong>Cocina</strong><em>Recetas, historias y tradición</em></a>
        </div>
    </div>
</section>

<section class="contact-cta">
    <div class="container contact-cta__inner" data-reveal>
        <div>
            <p class="eyebrow">¿Necesitas orientación?</p>
            <h2>Conversemos sobre tu próxima lectura.</h2>
        </div>
        <a class="button button--light" href="contacto.php">Enviar un mensaje</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
