<?php
session_start();

include_once "encabezado.php";

// --- DEBUG LOGGING START ---
$logFile = 'debug_login.log';
$msg = date("Y-m-d H:i:s") . " - Script reached. Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
if (!empty($_POST)) {
    $msg .= "POST Data received: " . print_r($_POST, true) . "\n";
} else {
    $msg .= "No POST data received.\n";
}
file_put_contents($logFile, $msg, FILE_APPEND);
// --- DEBUG LOGGING END ---

if (isset($_POST['nombre']) && isset($_POST['password'])) {
    if (empty($_POST['nombre']) || empty($_POST['password'])) {
        echo '
        <div class="alert alert-warning mt-3" role="alert">
            Debes completar todos los datos.
            <a href="login.php">Regresar</a>
        </div>';
        return;
    }

    include_once "funciones.php";

    $usuario = $_POST['nombre'];
    $password = $_POST['password'];

    $datosSesion = iniciarSesion($usuario, $password);

    if (!$datosSesion) {
        echo '
        <script>
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Nombre de usuario y/o contraseña incorrectas"
        }).then(() => {
            window.location.href = "login.php";
        });
        </script>';
        return;
    }

    $_SESSION['nombre'] = $datosSesion->nombre;
    $_SESSION['idUsuario'] = $datosSesion->id;
    header("location: index.php");
    exit;
}
