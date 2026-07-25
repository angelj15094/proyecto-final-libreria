<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Libros';
$pageDescription = 'Consulta todos los libros disponibles y filtra el catálogo por tema.';
$pdo = safe_db();
$libros = [];
$tipos = [];
$q = text_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$tipo = trim((string) ($_GET['tipo'] ?? ''));
$orden = (string) ($_GET['orden'] ?? 'titulo');
$ordenesPermitidos = [
    'titulo' => 't.titulo ASC',
    'precio_asc' => 't.precio IS NULL, t.precio ASC, t.titulo ASC',
    'precio_desc' => 't.precio IS NULL, t.precio DESC, t.titulo ASC',
    'recientes' => 't.fecha_pub DESC, t.titulo ASC',
];

if (!isset($ordenesPermitidos[$orden])) {
    $orden = 'titulo';
}

if ($pdo) {
    // Consulta fija con PDO query() para completar el filtro de categorías.
    $tipos = $pdo->query(
        "SELECT DISTINCT tipo
        FROM titulos
        WHERE tipo <> ''
        ORDER BY tipo"
    )->fetchAll(PDO::FETCH_COLUMN);

    if ($tipo !== '' && !in_array($tipo, $tipos, true)) {
        $tipo = '';
    }

    $sql = "SELECT
                t.id_titulo,
                t.titulo,
                t.tipo,
                t.precio,
                t.total_ventas,
                t.notas,
                t.fecha_pub,
                t.contrato,
                p.nombre_pub,
                COALESCE(
                    GROUP_CONCAT(
                        CONCAT(TRIM(a.nombre), ' ', TRIM(a.apellido))
                        ORDER BY CAST(ta.ord_au AS UNSIGNED)
                        SEPARATOR ', '
                    ),
                    'Autor no disponible'
                ) AS autores
            FROM titulos t
            LEFT JOIN publicadores p ON p.id_pub = t.id_pub
            LEFT JOIN titulo_autor ta ON ta.id_titulo = t.id_titulo
            LEFT JOIN autores a ON a.id_autor = ta.id_autor
            WHERE 1 = 1";
    $parameters = [];

    if ($q !== '') {
        $sql .= " AND (
            t.id_titulo LIKE :q_id
            OR t.titulo LIKE :q_titulo
            OR t.notas LIKE :q_notas
            OR EXISTS (
                SELECT 1
                FROM titulo_autor ta_busqueda
                INNER JOIN autores a_busqueda ON a_busqueda.id_autor = ta_busqueda.id_autor
                WHERE ta_busqueda.id_titulo = t.id_titulo
                AND CONCAT(TRIM(a_busqueda.nombre), ' ', TRIM(a_busqueda.apellido)) LIKE :q_autor
            )
        )";
        $searchTerm = '%' . $q . '%';
        $parameters = [
            'q_id' => $searchTerm,
            'q_titulo' => $searchTerm,
            'q_notas' => $searchTerm,
            'q_autor' => $searchTerm,
        ];
    }

    if ($tipo !== '') {
        $sql .= ' AND t.tipo = :tipo';
        $parameters['tipo'] = $tipo;
    }

    $sql .= " GROUP BY
                t.id_titulo,
                t.titulo,
                t.tipo,
                t.precio,
                t.total_ventas,
                t.notas,
                t.fecha_pub,
                t.contrato,
                p.nombre_pub
            ORDER BY {$ordenesPermitidos[$orden]}";

    $statement = $pdo->prepare($sql);
    $statement->execute($parameters);
    $libros = $statement->fetchAll();
}

$totalLibros = count($libros);
$totalTipos = sizeof($tipos);

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero--catalog">
    <div class="container page-hero__inner">
        <div data-reveal>
            <p class="eyebrow">Catálogo completo</p>
            <h1>Encuentra un libro para cada curiosidad.</h1>
            <p>Consulta todos los títulos disponibles, sus autores, categorías y datos editoriales.</p>
        </div>
        <div class="page-hero__aside" aria-hidden="true">
            <span><?= number_format($totalLibros) ?></span>
            <small>resultados</small>
        </div>
    </div>
</section>

<section class="catalog-section">
    <div class="container">
        <form class="filters" action="libros.php" method="get" role="search">
            <div class="field field--search">
                <label for="q">Buscar</label>
                <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Título, autor o palabra clave">
            </div>
            <div class="field">
                <label for="tipo">Categoría</label>
                <select id="tipo" name="tipo">
                    <option value="">Todas (<?= $totalTipos ?>)</option>
                    <?php foreach ($tipos as $opcionTipo): ?>
                        <option value="<?= e($opcionTipo) ?>" <?= $tipo === $opcionTipo ? 'selected' : '' ?>>
                            <?= e(format_book_type($opcionTipo)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="orden">Ordenar por</label>
                <select id="orden" name="orden">
                    <option value="titulo" <?= $orden === 'titulo' ? 'selected' : '' ?>>Título A–Z</option>
                    <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Menor precio</option>
                    <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Mayor precio</option>
                    <option value="recientes" <?= $orden === 'recientes' ? 'selected' : '' ?>>Publicación más reciente</option>
                </select>
            </div>
            <button class="button button--primary" type="submit">Aplicar filtros</button>
            <?php if ($q !== '' || $tipo !== '' || $orden !== 'titulo'): ?>
                <a class="filters__clear" href="libros.php">Limpiar</a>
            <?php endif; ?>
        </form>

        <div class="results-bar" aria-live="polite">
            <p>
                <strong><?= number_format($totalLibros) ?></strong>
                <?= $totalLibros === 1 ? 'libro encontrado' : 'libros encontrados' ?>
                <?php if ($q !== ''): ?>
                    para “<?= e($q) ?>”
                <?php endif; ?>
            </p>
        </div>

        <?php if (!$pdo): ?>
            <div class="status-panel status-panel--warning" role="status">
                <div>
                    <strong>No pudimos cargar el catálogo.</strong>
                    <p>La conexión con la biblioteca no está disponible en este momento.</p>
                </div>
            </div>
        <?php elseif ($libros): ?>
            <div class="book-grid">
                <?php foreach ($libros as $index => $libro): ?>
                    <article class="book-card" data-reveal>
                        <div class="catalog-cover catalog-cover--<?= e($libro['tipo']) ?>" aria-hidden="true">
                            <span><?= e($libro['id_titulo']) ?></span>
                            <strong><?= e(truncate_text($libro['titulo'], 42)) ?></strong>
                            <small>Horizonte</small>
                        </div>
                        <div class="book-card__content">
                            <div class="book-card__topline">
                                <span class="pill"><?= e(format_book_type($libro['tipo'])) ?></span>
                                <?php if ($libro['contrato'] !== '1'): ?>
                                    <span class="pill pill--muted">Próximamente</span>
                                <?php endif; ?>
                            </div>
                            <h2><?= e($libro['titulo']) ?></h2>
                            <p class="byline">Por <?= e($libro['autores']) ?></p>
                            <p class="book-card__description"><?= e(truncate_text($libro['notas'] ?: 'Descripción no disponible.', 165)) ?></p>
                            <dl class="book-meta">
                                <div>
                                    <dt>Editorial</dt>
                                    <dd><?= e($libro['nombre_pub'] ?: 'No disponible') ?></dd>
                                </div>
                                <div>
                                    <dt>Publicado</dt>
                                    <dd><?= e(date('Y', strtotime($libro['fecha_pub']))) ?></dd>
                                </div>
                            </dl>
                            <div class="book-card__footer">
                                <strong><?= e(format_price($libro['precio'])) ?></strong>
                                <span><?= number_format((int) ($libro['total_ventas'] ?? 0)) ?> ventas</span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span aria-hidden="true">⌕</span>
                <h2>No encontramos coincidencias</h2>
                <p>Prueba otra palabra o elimina alguno de los filtros seleccionados.</p>
                <a class="button button--secondary" href="libros.php">Ver todos los libros</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
