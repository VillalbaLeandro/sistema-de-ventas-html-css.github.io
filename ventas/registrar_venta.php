<?php
include_once "funciones.php";

session_start();
$productos = $_SESSION['lista'];
$idUsuario = $_SESSION['idUsuario'];
$total = calcularTotalLista($productos);
$idCliente = $_SESSION['clienteVenta'];
$medioPago = isset($_POST['mediopago']) ? $_POST['mediopago'] : 1;
$iva = isset($_POST['iva']) ? $_POST['iva'] : 2;


if (count($productos) === 0) {
    header("location: vender.php");
    return;
}

$idVenta = registrarVenta($idUsuario, $idCliente, $total, $medioPago, $iva);

if (!$idVenta) {
    echo "Error al registrar la venta";
    return;
}

$resultado = registrarProductosVenta($productos, $idVenta);

if (!$resultado) {
    echo "Error al registrar los productos vendidos";
    return;
}


$_SESSION['lista'] = [];
$_SESSION['clienteVenta'] = "";

echo "
<script type='text/javascript'>
    window.location.href='vender.php';
    alert('Venta realizada con éxito');
</script>";