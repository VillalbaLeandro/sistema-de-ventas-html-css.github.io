<?php
include("./head.php");
?>

<title>Formulario de Login</title>
<link rel="stylesheet" href="./public/css/header.css">
<link rel="stylesheet" href="./public/css/estilos-formularios.css">
</head>

<body>
    <?php
    include("./header.php");
    ?>
    <section class="form-registro"> <!-- Usa la misma clase que en register.php -->
        <h5>Formulario de Login</h5>
        <form class="form-login" action="./ventas/iniciar_sesion.php" method="POST">
            <input class="controls" type="text" name="nombre" placeholder="Usuario" required>
            <input class="controls" type="password" name="password" placeholder="Contraseña" required>
            <input class="boton" type="submit" value="Ingresar">
        </form>
        <p>No tienes una cuenta? <a href="./registro.php">Regístrate</a></p>
        <a class="boton2" href="index.php">Volver a la página principal</a>
    </section>
</body>