# Base de datos

Importe los archivos en este orden dentro de una base vacía llamada
`dblibreria`:

1. `Base Datos Libreria.sql`: volcado original suministrado con la práctica.
2. `01_contacto_y_compatibilidad.sql`: crea `contacto`, moderniza las tablas
   usadas por el portal y corrige cinco relaciones autor-libro dañadas en el
   archivo original.

El segundo archivo se ejecuta una sola vez. Antes de repetir una instalación,
elimine y vuelva a crear la base vacía.

La tabla `contacto` contiene los campos `id`, `fecha`, `correo`, `nombre`,
`asunto` y `comentario`. El documento original repite la palabra `correo`;
se interpreta como una errata porque un mismo formulario solo necesita una
dirección de respuesta.
