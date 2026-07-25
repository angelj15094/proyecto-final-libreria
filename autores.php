<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Autores';
$pageDescription = 'Conoce a todos los autores disponibles en el catálogo de Librería Horizonte.';
$pdo = safe_db();
$autores = [];
$estados = [];
$q = text_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$estado = text_upper(text_substr(trim((string) ($_GET['estado'] ?? '')), 0, 2));

if ($pdo) {
    $estados = $pdo->query(
        "SELECT DISTINCT estado
        FROM autores
        WHERE estado <> ''
        ORDER BY estado"
    )->fetchAll(PDO::FETCH_COLUMN);

    if ($estado !== '' && !in_array($estado, $estados, true)) {
        $estado = '';
    }

    $sql = "SELECT
                a.id_autor,
                TRIM(a.nombre) AS nombre,
                TRIM(a.apellido) AS apellido,
                a.telefono,
                TRIM(a.direccion) AS direccion,
                TRIM(a.ciudad) AS ciudad,
                a.estado,
                a.pais,
                a.cod_postal,
                b.biografia,
                COUNT(DISTINCT ta.id_titulo) AS total_titulos,
                GROUP_CONCAT(
                    DISTINCT t.titulo
                    ORDER BY t.titulo
                    SEPARATOR ' · '
                ) AS titulos
            FROM autores a
            LEFT JOIN biografias b ON b.id_autor = a.id_autor
            LEFT JOIN titulo_autor ta ON ta.id_autor = a.id_autor
            LEFT JOIN titulos t ON t.id_titulo = ta.id_titulo
            WHERE 1 = 1";
    $parameters = [];

    if ($q !== '') {
        $sql .= " AND (
            CONCAT(TRIM(a.nombre), ' ', TRIM(a.apellido)) LIKE :q_nombre
            OR a.ciudad LIKE :q_ciudad
            OR a.pais LIKE :q_pais
        )";
        $searchTerm = '%' . $q . '%';
        $parameters = [
            'q_nombre' => $searchTerm,
            'q_ciudad' => $searchTerm,
            'q_pais' => $searchTerm,
        ];
    }

    if ($estado !== '') {
        $sql .= ' AND a.estado = :estado';
        $parameters['estado'] = $estado;
    }

    $sql .= " GROUP BY
                a.id_autor,
                a.nombre,
                a.apellido,
                a.telefono,
                a.direccion,
                a.ciudad,
                a.estado,
                a.pais,
                a.cod_postal,
                b.biografia
            ORDER BY apellido ASC, nombre ASC";

    $statement = $pdo->prepare($sql);
    $statement->execute($parameters);
    $autores = $statement->fetchAll();
}

$totalAutores = count($autores);
$totalEstados = sizeof($estados);

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero--authors">
    <div class="container page-hero__inner">
        <div data-reveal>
            <p class="eyebrow">Directorio de autores</p>
            <h1>Las personas detrás de cada idea.</h1>
            <p>Descubre a quienes dan forma a los títulos de nuestro catálogo y explora sus publicaciones.</p>
        </div>
        <div class="page-hero__aside" aria-hidden="true">
            <span><?= number_format($totalAutores) ?></span>
            <small>autores</small>
        </div>
    </div>
</section>

<section class="catalog-section">
    <div class="container">
        <form class="filters filters--authors" action="autores.php" method="get" role="search">
            <div class="field field--search">
                <label for="q">Buscar autor</label>
                <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Nombre, ciudad o país">
            </div>
            <div class="field">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos (<?= $totalEstados ?>)</option>
                    <?php foreach ($estados as $opcionEstado): ?>
                        <option value="<?= e($opcionEstado) ?>" <?= $estado === $opcionEstado ? 'selected' : '' ?>>
                            <?= e($opcionEstado) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="button button--primary" type="submit">Buscar</button>
            <?php if ($q !== '' || $estado !== ''): ?>
                <a class="filters__clear" href="autores.php">Limpiar</a>
            <?php endif; ?>
        </form>

        <div class="results-bar" aria-live="polite">
            <p>
                <strong><?= number_format($totalAutores) ?></strong>
                <?= $totalAutores === 1 ? 'autor encontrado' : 'autores encontrados' ?>
                <?php if ($q !== ''): ?>
                    para “<?= e($q) ?>”
                <?php endif; ?>
            </p>
        </div>

        <?php if (!$pdo): ?>
            <div class="status-panel status-panel--warning" role="status">
                <div>
                    <strong>No pudimos cargar los autores.</strong>
                    <p>La conexión con la biblioteca no está disponible en este momento.</p>
                </div>
            </div>
        <?php elseif ($autores): ?>
            <div class="author-grid">
                <?php foreach ($autores as $autor): ?>
                    <?php
                    $iniciales = text_upper(
                        text_substr($autor['nombre'], 0, 1) . text_substr($autor['apellido'], 0, 1)
                    );
                    ?>
                    <article class="author-card" data-reveal>
                        <header class="author-card__header">
                            <div class="author-avatar" aria-hidden="true"><?= e($iniciales) ?></div>
                            <div>
                                <span class="pill">
                                    <?= (int) $autor['total_titulos'] ?>
                                    <?= (int) $autor['total_titulos'] === 1 ? 'título' : 'títulos' ?>
                                </span>
                                <h2><?= e($autor['nombre'] . ' ' . $autor['apellido']) ?></h2>
                                <p><?= e($autor['ciudad'] . ', ' . $autor['estado'] . ' · ' . $autor['pais']) ?></p>
                            </div>
                        </header>

                        <?php if (!empty($autor['biografia'])): ?>
                            <p class="author-card__bio"><?= e(truncate_text($autor['biografia'], 180)) ?></p>
                        <?php else: ?>
                            <p class="author-card__bio author-card__bio--muted">
                                Biografía no disponible. Consulta sus títulos para conocer su trabajo.
                            </p>
                        <?php endif; ?>

                        <div class="author-card__books">
                            <span>Publicaciones</span>
                            <p><?= e($autor['titulos'] ?: 'Sin títulos asociados') ?></p>
                        </div>

                        <footer class="author-card__footer">
                            <span><?= e($autor['telefono']) ?></span>
                            <?php if ((int) $autor['total_titulos'] > 0): ?>
                                <a href="libros.php?q=<?= urlencode($autor['nombre'] . ' ' . $autor['apellido']) ?>">
                                    Ver libros <span aria-hidden="true">→</span>
                                </a>
                            <?php endif; ?>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span aria-hidden="true">⌕</span>
                <h2>No encontramos coincidencias</h2>
                <p>Prueba con otro nombre o elimina el filtro seleccionado.</p>
                <a class="button button--secondary" href="autores.php">Ver todos los autores</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
