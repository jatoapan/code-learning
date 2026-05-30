# Carpeta de Plugins Personalizados de Moodle

Coloca aquí los plugins que tu equipo necesite para el proyecto. El pipeline de despliegue en Railway los copiará automáticamente a la estructura interna de Moodle en cada build.

## Estructura requerida:
Debes colocar los plugins dentro de la subcarpeta que corresponda a su tipo, imitando la estructura de Moodle:

* **Módulos de actividad (Activity modules):** `plugins/mod/nombre_del_plugin/`
* **Temas visuales (Themes):** `plugins/theme/nombre_del_tema/`
* **Bloques (Blocks):** `plugins/blocks/nombre_del_bloque/`
* **Filtros (Filters):** `plugins/filter/nombre_del_filtro/`
* **Plugins locales:** `plugins/local/nombre_del_plugin/`

### Ejemplo:
Si tienes un plugin de tipo módulo llamado `asistencia`, su ruta en este repositorio debe ser:
`plugins/mod/asistencia/version.php`, `plugins/mod/asistencia/index.php`, etc.
