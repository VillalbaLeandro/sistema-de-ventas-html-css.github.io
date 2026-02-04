<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Credenciales LOCALES (Laragon) - Descomentar para trabajar en local
$Servidor = "localhost";
$Base_Datos = "drinkstore_db";
$usuario = "root";
$clave = null;

// Credenciales PRODUCTIVO (InfinityFree)
// $Servidor = "sql300.infinityfree.com";
// $Base_Datos = "if0_41066341_drinkstore_db";
// $usuario = "if0_41066341";
// $clave = "481bQ5aGIR6z4p";

$conexion = new mysqli($Servidor, $usuario, $clave, $Base_Datos);
$conexion->set_charset("utf8");

// Verificar si hay errores de conexión
if ($conexion->connect_errno) {
    die("Error en la conexión a la base de datos: " . $conexion->connect_error);
}

