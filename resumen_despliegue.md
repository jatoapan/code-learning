# Resumen del Proceso de Despliegue de Moodle en Railway

Este documento detalla paso a paso la cronología de eventos, problemas técnicos identificados y las soluciones de arquitectura aplicadas para desplegar de manera exitosa **Moodle 5.3dev** usando **Docker** y **Railway** con una base de datos **MySQL**.

---

## 📋 Resumen Cronológico de Pasos y Resoluciones

### Paso 1: Reorganización Limpia del Repositorio (`backend/`)
* **Qué sucedió:** Para permitir la incorporación futura de otros servicios (como una aplicación de frontend independiente), se decidió aislar todo el código de Moodle.
* **Resolución:** Se creó una carpeta raíz llamada `backend/` y se reubicó todo el código fuente de Moodle dentro de ella, manteniendo el historial limpio mediante Git. Adicionalmente, se creó un `.gitignore` en la raíz del repositorio para evitar el seguimiento de archivos temporales del asistente.

---

### Paso 2: Solución al Límite de Versión de PHP (Fallo de Nixpacks)
* **Qué sucedió:** Sin un archivo de configuración explícito, el motor por defecto de Railway (Nixpacks) compiló el proyecto usando PHP 8.5.6. Esto causó un fallo crítico de dependencias en Composer porque Moodle 5.3dev requiere una versión máxima de PHP 8.4.
* **Resolución:** Se creó el archivo `backend/Dockerfile` utilizando la imagen oficial optimizada **`php:8.3-apache`**, lo que forzó a Railway a usar PHP 8.3 de forma estable.

---

### Paso 3: Resolución del Conflicto de Motores de Apache (Error MPM)
* **Qué sucedió:** Apache arrojaba el error `AH00534: apache2: Configuration error: More than one MPM loaded` al arrancar el contenedor en Railway. Ocurrió porque Railway forzaba el inicio del motor de eventos (`mpm_event`), mientras que la imagen de PHP cargaba por defecto el motor compatible con PHP (`mpm_prefork`). Apache no permite ejecutar dos motores simultáneamente.
* **Resolución:** Se configuró un script de inicio dinámico (`docker-entrypoint.sh`) dentro del `Dockerfile` que se ejecuta en tiempo de ejecución (runtime) para desactivar explícitamente `mpm_event` y `mpm_worker`, asegurando que Apache use únicamente `mpm_prefork` de manera estable.

---

### Paso 4: Ajuste del Límite de Variables de PHP (`max_input_vars`)
* **Qué sucedió:** Moodle arrojó un bloqueo obligatorio de instalación debido a que el límite predeterminado de variables de formulario en PHP es de 1000 y Moodle exige un mínimo de **5000** (`max_input_vars`).
* **Resolución:** Se modificó el `Dockerfile` para inyectar un archivo de configuración de PHP personalizado (`docker-php-moodle.ini`) estableciendo `max_input_vars = 5000` y optimizando de paso el aviso de seguridad `zend.exception_ignore_args = On`.

---

### Paso 5: Corrección del Error de Cambios de IP (`ignoreipchecks`)
* **Qué sucedió:** Al avanzar en el asistente web, Moodle bloqueó la instalación con el error: *`Installation must be finished from the original IP address`*. Ocurrió porque los proxies de Railway cambian de IP interna en cada petición web, haciendo que Moodle detectara un "cambio de dirección de cliente" sospechoso.
* **Resolución:** Se configuró el parámetro `$CFG->ignoreipchecks = true;` en el archivo `backend/config.php` dinámico. Esto instruye a Moodle a omitir la validación rígida de IP de la sesión del navegador durante esta fase, lo que permitió finalizar con éxito la instalación.

---

## 🛠️ Estado Actual y Funcionamiento del Sistema

1. **`config.php` Dinámico:** Ubicado en `backend/config.php`. Utiliza variables de entorno (`getenv()`) para conectarse de forma **privada y segura** (DNS interno `*.railway.internal`) al nodo de base de datos MySQL en Railway, sin exponer credenciales a internet ni incurrir en cobros de salida de datos (egress fees).
2. **Volumen Persistente:** Mapeado en la ruta `/var/moodledata` dentro del contenedor. El script de arranque (`entrypoint`) ajusta automáticamente sus permisos en cada inicio (`chown www-data:www-data`), asegurando que todos los archivos, tareas y PDFs subidos al aula virtual se mantengan a salvo permanentemente tras actualizaciones.
3. **Flujo de Trabajo:** El equipo solo debe empujar cambios a la rama principal de Git. Railway se encargará de compilar, probar e implementar las actualizaciones automáticamente sin fricción.
