-- Ejecutar después de "Base Datos Libreria.sql".
-- Adecuación mínima para MySQL 8/MariaDB 11 y corrección de relaciones dañadas.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- La base suministrada contiene cuatro identificadores recortados o dañados.
UPDATE autores SET id_autor = '172-32-1176' WHERE id_autor = '172-32-117';
UPDATE autores SET id_autor = '213-46-8915' WHERE id_autor = '213-46-891';
UPDATE autores SET id_autor = '238-95-7766' WHERE id_autor = '238-95-776';
UPDATE autores SET id_autor = '267-41-2394' WHERE id_autor = '267-41-239O';

-- Restaura la fila ausente del conjunto de ejemplo Pubs.
INSERT INTO autores (
    id_autor,
    apellido,
    nombre,
    telefono,
    direccion,
    ciudad,
    estado,
    pais,
    cod_postal
)
SELECT
    '427-17-2319',
    'Dull',
    'Ann',
    '415 836-7128',
    '591-62th St.',
    'Palo Alto',
    'CA',
    'USA',
    94301
WHERE NOT EXISTS (
    SELECT 1 FROM autores WHERE id_autor = '427-17-2319'
);

-- La cabecera 234518 apunta a la tienda 380, mientras sus detalles usan 6380.
UPDATE ventas
SET id_tienda = '6380'
WHERE id_tienda = '380' AND num_orden = '234518';

-- Evita pérdidas futuras y moderniza las tablas utilizadas por el portal.
ALTER TABLE autores
    ENGINE = InnoDB,
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE biografias
    MODIFY biografia TEXT NOT NULL,
    ENGINE = InnoDB,
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE publicadores
    ENGINE = InnoDB,
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE titulos
    MODIFY titulo VARCHAR(120) NOT NULL,
    MODIFY precio DECIMAL(10, 2) NULL,
    MODIFY avance DECIMAL(12, 2) NULL,
    MODIFY notas TEXT NOT NULL,
    ENGINE = InnoDB,
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE titulo_autor
    ENGINE = InnoDB,
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX idx_titulo_autor_titulo_orden
    ON titulo_autor (id_titulo, ord_au);

CREATE TABLE contacto (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    correo VARCHAR(254) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    comentario TEXT NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_contacto_fecha (fecha),
    INDEX idx_contacto_correo (correo)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
