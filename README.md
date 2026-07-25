# Librería Horizonte

Portal académico desarrollado con HTML, CSS, JavaScript, PHP, PDO y MySQL.
Permite consultar todos los libros y autores de la base suministrada y guardar
mensajes enviados desde un formulario de contacto.

Repositorio público: https://github.com/angelj15094/proyecto-final-libreria

## Funciones

- Inicio con resumen del catálogo y libros destacados.
- Listado completo de libros.
- Búsqueda por título, autor, código o palabra clave.
- Filtro por categoría y orden por título, precio o fecha.
- Listado completo de autores, búsqueda y filtro por estado.
- Formulario de contacto con validación en cliente y servidor.
- Persistencia de contactos mediante una consulta preparada con PDO.
- Protección CSRF, campo antispam y escape de salida HTML.
- Diseño responsive y navegación accesible.
- Interfaz completamente en español.

## Requisitos cubiertos

| Requisito académico | Implementación |
|---|---|
| HTML, CSS y JavaScript | Plantillas PHP semánticas, hoja de estilos propia e interacciones en `assets/js/app.js` |
| PHP y MySQL | Aplicación PHP 8 con base `dblibreria` |
| PDO / PDO query | Conexión central PDO; `query()` para consultas fijas y `prepare()` para datos externos |
| GET | Búsquedas, filtros y orden en libros/autores |
| POST | Envío del formulario de contacto |
| Foreach | Renderizado de libros, autores, categorías y estados |
| Count / Size_of | Conteo de resultados y filtros disponibles |
| Tabla contacto | Migración con `id`, `fecha`, `correo`, `nombre`, `asunto`, `comentario` |

El documento entregado repite `correo` en la lista de campos de contacto. Se
implementó una sola columna `correo`, interpretando la repetición como errata.

## Instalación local con Docker

1. Inicie Docker.
2. Desde esta carpeta ejecute `docker compose up --build`.
3. Abra `http://localhost:8080`.

La primera ejecución importa automáticamente el SQL original y luego la
migración de compatibilidad. Para repetir una instalación limpia, ejecute
`docker compose down -v` y vuelva a iniciar.

Prueba rápida:

```bash
docker compose exec web php tests/smoke.php
```

## Instalación en un hosting PHP/MySQL

1. Cree una base de datos MySQL vacía.
2. Desde phpMyAdmin importe, en orden:
   - `database/Base Datos Libreria.sql`
   - `database/01_contacto_y_compatibilidad.sql`
3. Copie `config/database.local.example.php` como
   `config/database.local.php`.
4. Complete en ese archivo el host, puerto, base, usuario y contraseña reales.
5. Suba el resto de los archivos al directorio público del hosting.
6. No suba `config/database.local.php` al repositorio público.

## Estructura principal

```text
assets/       Estilos y JavaScript
config/       Conexión PDO
database/     SQL original y migración
includes/     Plantillas y funciones compartidas
tests/        Prueba automatizada de base de datos
index.php     Inicio
libros.php    Catálogo y filtros
autores.php   Directorio de autores
contacto.php  Formulario y persistencia
```

## Publicación recomendada

El proyecto está preparado para un hosting gratuito con PHP 8 y MySQL, como
InfinityFree. El repositorio público debe excluir toda contraseña mediante el
archivo `.gitignore`.
