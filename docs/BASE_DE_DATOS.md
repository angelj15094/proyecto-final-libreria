# Base de datos del proyecto

El portal utiliza la base de datos de librería proporcionada en clase, importada desde el archivo `Base Datos Libreria.sql`.

## Tabla adicional

Se creó la tabla `contacto` dentro de la misma base de datos para almacenar los mensajes enviados por los visitantes. Sus campos principales son:

- `id`: identificador del mensaje.
- `fecha`: fecha y hora del envío.
- `correo`: correo del visitante.
- `nombre`: nombre del visitante.
- `asunto`: tema del mensaje.
- `comentario`: contenido escrito por el usuario.

Los datos se insertan y consultan desde PHP mediante PDO y consultas preparadas.
