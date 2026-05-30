<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = getenv('MYSQLHOST') ?: '127.0.0.1';
$CFG->dbname    = getenv('MYSQLDATABASE') ?: 'moodle';
$CFG->dbuser    = getenv('MYSQLUSER') ?: 'moodle';
$CFG->dbpass    = getenv('MYSQLPASSWORD') ?: 'moodle_pass';
$CFG->dbport    = getenv('MYSQLPORT') ?: 3306;
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => getenv('MYSQLPORT') ?: 3306,
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_unicode_ci',
);

// URL del sitio. En local se usa localhost:8080.
// En Railway se debe configurar la variable de entorno MOODLE_URL (ej. https://tu-app.up.railway.app)
$CFG->wwwroot   = getenv('MOODLE_URL') ?: 'http://localhost:8080';

// Directorio de datos
$CFG->dataroot  = getenv('MOODLE_DATAROOT') ?: '/var/moodledata';

$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

// Habilitar sslproxy si la URL empieza con https (necesario detrás del balanceador de Railway)
if (getenv('MOODLE_SSLPROXY') === 'true' || (getenv('MOODLE_URL') && strpos(getenv('MOODLE_URL'), 'https://') === 0)) {
    $CFG->sslproxy = true;
    $CFG->ignoreipchecks = true;
}

require_once(__DIR__ . '/public/lib/setup.php');
