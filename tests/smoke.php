<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function assert_same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            sprintf('%s. Esperado: %s; recibido: %s', $message, var_export($expected, true), var_export($actual, true))
        );
    }
}

$pdo = db();

assert_same(18, (int) $pdo->query('SELECT COUNT(*) FROM titulos')->fetchColumn(), 'Cantidad de libros incorrecta');
assert_same(23, (int) $pdo->query('SELECT COUNT(*) FROM autores')->fetchColumn(), 'Cantidad de autores incorrecta');
assert_same(25, (int) $pdo->query('SELECT COUNT(*) FROM titulo_autor')->fetchColumn(), 'Cantidad de relaciones incorrecta');
assert_same(
    0,
    (int) $pdo->query(
        'SELECT COUNT(*)
        FROM titulo_autor ta
        LEFT JOIN autores a ON a.id_autor = ta.id_autor
        WHERE a.id_autor IS NULL'
    )->fetchColumn(),
    'Existen relaciones autor-libro huérfanas'
);

$pdo->beginTransaction();
$statement = $pdo->prepare(
    'INSERT INTO contacto (fecha, correo, nombre, asunto, comentario)
    VALUES (NOW(), :correo, :nombre, :asunto, :comentario)'
);
$statement->execute([
    'correo' => 'prueba@example.com',
    'nombre' => 'Prueba automática',
    'asunto' => 'Validación del formulario',
    'comentario' => 'Este registro se elimina al finalizar la prueba.',
]);
assert_same(1, $statement->rowCount(), 'No se pudo insertar un contacto');
$pdo->rollBack();

echo "OK: base, relaciones y contacto validados.\n";
