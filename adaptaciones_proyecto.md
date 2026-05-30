# Guía de Adaptaciones, Endpoints y Frontend para Prolecom

Este documento contiene la hoja de ruta técnica para el equipo de desarrollo de la ESPOL. Detalla cómo extender el backend de Moodle para cumplir con los requerimientos del PDF, los endpoints listos para consumir y cómo iniciar el desarrollo del frontend desacoplado en ReactJS.

---

## 🛠️ 1. Adaptaciones y Plugins requeridos en el Backend (Moodle)

Moodle 5.3 actuará como un **Headless CMS/LMS** (proveedor de base de datos y lógica de negocio). Para implementar las características específicas del documento de planificación, se deben instalar o configurar los siguientes módulos en las subcarpetas del directorio `/backend`:

### A. Entorno de Retos de Programación (IDE y Desafíos)
*   **Requerimiento del PDF:** Un banco de desafíos de código en 3 dificultades con editor visual (sintaxis de Python) e ingreso de casos de prueba.
*   **Solución en Moodle:** Instalar el plugin oficial **VPL (Virtual Programming Lab)**. Proporciona un IDE basado en Monaco/Ace Editor en el navegador y un servidor de ejecución sandbox.
*   **Ubicación en el repositorio:** Copiar el plugin VPL dentro de:
    `backend/plugins/mod/vpl/` (se instalará automáticamente en `backend/public/mod/vpl/` al hacer push).

### B. Foro de Preguntas y Respuestas (Q&A) con Votaciones
*   **Requerimiento del PDF:** Un foro de discusión donde los estudiantes hagan preguntas, los profesores/ayudantes validen la mejor respuesta (Mark as Useful) y los usuarios voten.
*   **Solución en Moodle:** Moodle posee el módulo nativo **`mod_forum`** con el formato de foro "Q&A Forum". 
*   **Modificación:** Para habilitar votaciones de posts y marcar respuestas útiles de manera avanzada (estilo StackOverflow), se recomienda instalar el plugin **Moodle Q&A Forum** o configurar ratings con escala numérica en el foro nativo.
*   **Ubicación de plugins adicionales de foro:** `backend/plugins/mod/forumqa/` (si deciden no usar el nativo).

### C. Sistema de Mensajería (Chat Asíncrono)
*   **Requerimiento del PDF:** Mapear chats entre estudiantes, profesores y ayudantes.
*   **Solución en Moodle:** Usar el sistema de chat y mensajería instantánea nativo de Moodle (`core_message`). Ya viene preinstalado y tiene endpoints REST de forma nativa.

---

## 🔌 2. Endpoints de la API de Moodle listos para usar

Moodle expone todas sus funciones mediante Web Services. **(Estado: ¡Activado y listo para consumir!)** Ya se han habilitado las funciones de Web Services a nivel global, el protocolo REST y la integración para dispositivos móviles en el panel de administración.

### A. Autenticación y Token de Usuario
Para iniciar sesión desde React y obtener el token de seguridad:
*   **Endpoint (POST):**
    `https://code-learning-staging.up.railway.app/login/token.php`
*   **Parámetros:** `username`, `password`, `service=moodle_mobile_app`
*   **Respuesta:** Devuelve un `token` que React debe almacenar en `localStorage` o en una cookie segura.

### B. Funciones REST principales para los Casos de Uso
Toda petición REST a Moodle se envía a:
`https://code-learning-staging.up.railway.app/webservice/rest/server.php?wstoken=TU_TOKEN&wsfunction=NOMBRE_FUNCION&moodleformat=json`

| Módulo del PDF | Función de Web Service de Moodle | Descripción |
| :--- | :--- | :--- |
| **Autenticación** | `core_user_get_users_by_field` | Obtiene el perfil del estudiante logueado (Username, Email, Rol). |
| **Cursos (Fig 9.2)** | `core_course_get_courses` | Obtiene la lista completa de cursos disponibles (Fundamentos de Python). |
| **Matrícula (Fig 9.3)** | `enrol_manual_enrol_users` | Matricula al estudiante en un curso determinado. |
| **Materiales (Fig 9.4)**| `core_course_get_contents` | Lista los archivos, PDFs y enlaces del curso por temas. |
| **Foro Q&A (Fig 9.5)** | `mod_forum_get_forum_discussions` | Obtiene las discusiones y preguntas publicadas en el foro del curso. |
| **Foro Q&A (Fig 9.6)** | `mod_forum_add_discussion` | Permite al estudiante publicar una nueva pregunta. |
| **Foro Q&A (Fig 9.7)** | `mod_forum_add_discussion_post` | Permite responder a una pregunta en el foro. |
| **Quizzes (Fig 9.11)** | `mod_quiz_get_quizzes_by_courses` | Lista las evaluaciones publicadas por el docente. |
| **Mensajería (Chat)** | `core_message_send_instant_messages` | Envía un mensaje privado a un ayudante o profesor. |

---

## ⚛️ 3. Estructura y Tecnologías para el Frontend (ReactJS)

Tal como solicita tu proyecto, el frontend debe ser desarrollado en **ReactJS** de manera desacoplada. 

### A. Estructura del Repositorio sugerida
Recomiendo inicializar la carpeta `frontend/` en la raíz del proyecto para que la estructura quede así:
```text
code-learning/
├── backend/            <-- Moodle (desplegado en Railway)
├── frontend/           <-- ReactJS (tu interfaz personalizada)
│   ├── src/
│   │   ├── components/ <-- Elementos visuales reutilizables (Botones, Inputs, Cards de cursos)
│   │   ├── views/      <-- Vistas de Prototipo (StudentDashboard, EditorIDE, ForumQ&A)
│   │   ├── services/   <-- Funciones Fetch/Axios para conectarse a los Endpoints de Moodle
│   │   ├── App.jsx
│   │   └── main.jsx
│   ├── package.json
│   └── vite.config.js
└── resumen_despliegue.md
```

### B. Tecnologías clave a implementar en el Frontend:
1.  **Editor de Código (IDE Visual):** Para el editor de retos de programación (Python), puedes integrar la librería **`@monaco-editor/react`** (el mismo editor de VS Code para React) o **`react-codemirror`**, que son ligeras y soportan resaltado de sintaxis de Python de forma nativa en el navegador sin backend pesado de ejecución.
2.  **Rutas:** Usar **`react-router-dom`** para navegar entre las vistas (Login ➔ Dashboard Estudiante ➔ Dashboard Profesor).
3.  **Cliente API:** Usar **`axios`** o `fetch` para realizar las llamadas HTTP a tu Railway.

---
