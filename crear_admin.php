<?php
include_once "ventas/funciones.php";

$nombre = "admin1";
$password = "admin1";
$passwordHasheada = password_hash($password, PASSWORD_DEFAULT);

$sentencia = "INSERT INTO usuario (nombre, apellido, tel, direccion, rol_id, email, pass) VALUES (?, ?, ?, ?, ?, ?, ?)";
$parametros = [$nombre, 'Admin', '12345678', 'Direccion Admin', 1, 'admin1@test.com', $passwordHasheada];

if (insertar($sentencia, $parametros)) {
    echo "<h1>¡Usuario creado con éxito!</h1>";
    echo "<p>Usuario: <strong>$nombre</strong></p>";
    echo "<p>Contraseña: <strong>$password</strong></p>";
    echo "<br><a href='login.php'>Ir al Login</a>";
} else {
    echo "<h1>Error al crear usuario</h1>";
}
?>