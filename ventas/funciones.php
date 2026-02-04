<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
define("PASSWORD_PREDETERMINADA", "admin");
define("HOY", date("Y-m-d"));
function select($sentencia, $parametros = [])
{
    $bd = conectarBaseDatos();
    $respuesta = $bd->prepare($sentencia);
    $respuesta->execute($parametros);
    return $respuesta->fetchAll();
}
function obtenerTotalVentas($idUsuario = null)
{
    $parametros = [];
    $sentencia = "SELECT IFNULL(SUM(total), 0) AS total FROM venta";
    if (isset($idUsuario)) {
        $sentencia .= " WHERE usuario_id = ?";
        array_push($parametros, $idUsuario);
    }
    $fila = select($sentencia, $parametros);
    if ($fila)
        return number_format($fila[0]->total, 2);
}

function obtenerTotalVentasHoy($idUsuario = null)
{
    $parametros = [];
    $sentencia = "SELECT IFNULL(SUM(total), 0) AS total FROM venta WHERE DATE(fecha) = CURDATE()";
    if (isset($idUsuario)) {
        $sentencia .= " AND usuario_id = ?";
        array_push($parametros, $idUsuario);
    }
    $fila = select($sentencia, $parametros);
    if ($fila)
        return number_format($fila[0]->total, 2);
}
function obtenerTotalVentasSemana($idUsuario = null)
{
    $parametros = [];
    $sentencia = "SELECT IFNULL(SUM(total), 0) AS total FROM venta WHERE WEEK(fecha) = WEEK(NOW())";
    if (isset($idUsuario)) {
        $sentencia .= " AND usuario_id = ?";
        array_push($parametros, $idUsuario);
    }
    $fila = select($sentencia, $parametros);
    if ($fila)
        return number_format($fila[0]->total, 2);
}


function obtenerTotalVentasMes($idUsuario = null)
{
    $parametros = [];
    $sentencia = "SELECT IFNULL(SUM(total), 0) AS total FROM venta WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())";
    if (isset($idUsuario)) {
        $sentencia .= " AND usuario_id = ?";
        array_push($parametros, $idUsuario);
    }
    $fila = select($sentencia, $parametros);
    if ($fila)
        return number_format($fila[0]->total, 2);
}

// Función para obtener todos los productos de la base de datos
function obtenerTodosLosProductos()
{
    $conexion = conectarBaseDatos(); // Asegúrate de tener una función de conexión a la base de datos
    $sql = "SELECT id, codigo, nombre, descripcion FROM producto ORDER BY nombre ASC"; // Ajusta el nombre de la tabla y columnas según tu estructura
    $sentencia = $conexion->prepare($sql);
    $sentencia->execute();
    return $sentencia->fetchAll(PDO::FETCH_OBJ);
}

// PROCEDIMIENTO ALMACENADO 
function obtenerNumeroProductos()
{
    try {
        $conexion = conectarBaseDatos();

        $stmt = $conexion->prepare("CALL ObtenerNumeroProductos(@totalProductos)");
        $stmt->execute();

        $stmt = $conexion->query("SELECT @totalProductos AS totalProductos");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalProductos = $resultado['totalProductos'];

        return $totalProductos;
    } catch (PDOException $e) {
        // echo "Error al obtener el número de productos: " . $e->getMessage();
        return 0;
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}
// Función para obtener el siguiente código de producto basado en el último registrado
function obtenerSiguienteCodigoProducto()
{
    $conexion = conectarBaseDatos();
    $sql = "SELECT MAX(codigo) AS max_codigo FROM producto";
    $sentencia = $conexion->prepare($sql);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_OBJ);

    // Si no hay productos, el primer código será 1000
    $ultimoCodigo = isset($resultado->max_codigo) ? (int) $resultado->max_codigo : 999;

    // Retorna el siguiente código incrementando el último por 1
    return $ultimoCodigo + 1;
}

function obtenerNumeroVentas()
{
    $sentencia = "SELECT IFNULL(COUNT(*),0) AS total FROM venta";
    return select($sentencia)[0]->total;
}

function obtenerNumeroUsuarios()
{
    $sentencia = "SELECT IFNULL(COUNT(*),0) AS total FROM usuario";
    return select($sentencia)[0]->total;
}

function obtenerNumeroClientes()
{
    $sentencia = "SELECT IFNULL(COUNT(*),0) AS total FROM cliente";
    return select($sentencia)[0]->total;
}
function obtenerVentasPorUsuario()
{
    $sentencia = "SELECT usuario.nombre AS usuario, COUNT(*) AS numeroVentas, IFNULL(SUM(detalle_venta.cantidad * producto.precio_venta), 0) AS total
    FROM venta
    INNER JOIN usuario ON usuario.id = venta.usuario_id
    INNER JOIN detalle_venta ON venta.id = detalle_venta.venta_id
    INNER JOIN producto ON detalle_venta.producto_id = producto.id
    GROUP BY venta.usuario_id
    ORDER BY total DESC";
    return select($sentencia);
}

function obtenerVentasPorCliente()
{
    $sentencia = "SELECT IFNULL(cliente.nombre, 'MOSTRADOR') AS cliente, COUNT(*) AS numeroCompras, SUM(venta.total) AS total
    FROM venta
    LEFT JOIN cliente ON cliente.id = venta.cliente_id
    GROUP BY venta.cliente_id
    ORDER BY total DESC";
    return select($sentencia);
}


function obtenerProductosMasVendidos()
{
    $sentencia = "SELECT SUM(detalle_venta.cantidad) AS unidades, SUM(detalle_venta.cantidad * producto.precio_venta) AS total, producto.nombre
    FROM detalle_venta
    INNER JOIN producto ON detalle_venta.producto_id = producto.id
    GROUP BY detalle_venta.producto_id
    ORDER BY total DESC
    LIMIT 10";
    return select($sentencia);
}
function buscarProductos($search)
{
    $pdo = conectarBaseDatos();

    if (!$pdo) {
        // Manejar el error de conexión a la base de datos
        return array();
    }

    try {
        // Consulta SQL para buscar productos por código o nombre
        $sql = "SELECT * FROM producto WHERE nombre LIKE :search_nombre OR codigo LIKE :search_codigo";
        $statement = $pdo->prepare($sql);
        $statement->execute(array(':search_nombre' => '%' . $search . '%', ':search_codigo' => '%' . $search . '%'));

        // Recopila los resultados en un arreglo
        $productos = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $productos;
    } catch (PDOException $e) {
        // Manejar el error de la consulta SQL
        return array();
    }
}



function obtenerProductos($busqueda = null)
{
    $parametros = [];
    $sentencia = "SELECT * FROM producto ";
    if (isset($busqueda)) {
        $sentencia .= " WHERE nombre LIKE ? OR codigo LIKE ?";
        array_push($parametros, "%" . $busqueda . "%", "%" . $busqueda . "%");
    }
    return select($sentencia, $parametros);
}

function obtenerTotalInventario()
{
    $sentencia = "SELECT IFNULL(SUM(stock * precio_venta),0) AS total FROM producto";
    $fila = select($sentencia);
    if ($fila)
        return $fila[0]->total;
}


function calcularGananciaProductos()
{
    $sentencia = "SELECT IFNULL(SUM(stock * precio_venta) - SUM(stock*precio_costo),0) AS total FROM producto";
    $fila = select($sentencia);
    if ($fila)
        return $fila[0]->total;
}
function insertar($sentencia, $parametros)
{
    $bd = conectarBaseDatos();
    $respuesta = $bd->prepare($sentencia);
    return $respuesta->execute($parametros);
}
function registrarProducto($codigo, $nombre, $descripcion, $categoria)
{
    $sentencia = "INSERT INTO producto(codigo, nombre, descripcion, categoria_id, precio_costo, precio_venta, stock) VALUES (?, ?, ?, ?, 0, 0, 0)";
    $parametros = [$codigo, $nombre, $descripcion, $categoria];
    return insertar($sentencia, $parametros);
}


function obtenerCategorias()
{

    $sentencia = "SELECT id, nombre FROM categoria ORDER BY nombre";

    $parametros = [];

    return select($sentencia, $parametros);
}
function obtenerCategoriaIVADeCliente($categoriaClienteId)
{
    $sentencia = "SELECT id, nombre FROM categoria_cliente WHERE id = ?";
    $parametros = [$categoriaClienteId];
    $categoria = select($sentencia, $parametros);

    if (!empty($categoria)) {
        return $categoria[0];
    } else {
        return null;
    }
}
// Función para obtener una categoría por su ID
function obtenerCategoriaPorId($id)
{
    $conexion = conectarBaseDatos();
    $sql = "SELECT * FROM categoria WHERE id = ?";
    $sentencia = $conexion->prepare($sql);
    $sentencia->execute([$id]);
    return $sentencia->fetch(PDO::FETCH_OBJ);
}

// Función para agregar una nueva categoría
function agregarCategoria($nombre)
{
    $conexion = conectarBaseDatos();
    $sql = "INSERT INTO categoria (nombre) VALUES (?)";
    $sentencia = $conexion->prepare($sql);
    return $sentencia->execute([$nombre]);
}

// Función para editar una categoría
function editarCategoria($id, $nombre)
{
    $conexion = conectarBaseDatos();
    $sql = "UPDATE categoria SET nombre = ? WHERE id = ?";
    $sentencia = $conexion->prepare($sql);
    return $sentencia->execute([$nombre, $id]);
}

// Función para eliminar una categoría
function eliminarCategoria($id)
{
    $conexion = conectarBaseDatos();
    $sql = "DELETE FROM categoria WHERE id = ?";
    $sentencia = $conexion->prepare($sql);
    return $sentencia->execute([$id]);
}


function obtenerNumeroFactura()
{
    $pdo = conectarBaseDatos();

    $sql = "SELECT numero FROM contador_facturas WHERE id = 1";
    $statement = $pdo->query($sql);
    $resultado = $statement->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $numeroFactura = $resultado['numero'];
    } else {
        $numeroFactura = 1;
    }

    return $numeroFactura;
}

function incrementarNumeroFactura()
{
    $pdo = conectarBaseDatos();

    $numeroFactura = obtenerNumeroFactura();

    $nuevoNumeroFactura = $numeroFactura + 1;

    $sql = "UPDATE contador_facturas SET numero = :nuevoNumero WHERE id = 1";
    $statement = $pdo->prepare($sql);
    $statement->execute(['nuevoNumero' => $nuevoNumeroFactura]);

    return $nuevoNumeroFactura;
}
function obtenerProductoPorId($id)
{
    $sentencia = "SELECT * FROM producto WHERE id = ?";
    return select($sentencia, [$id])[0];
}
function editar($sentencia, $parametros)
{
    $bd = conectarBaseDatos();
    $respuesta = $bd->prepare($sentencia);
    return $respuesta->execute($parametros);
}
function editarProducto($codigo, $nombre, $descripcion, $categoria_id, $precio_costo, $precio_venta, $stock_minimo, $id)
{
    $conexion = conectarBaseDatos();
    $sql = "UPDATE producto SET codigo = ?, nombre = ?, descripcion = ?, categoria_id = ?, precio_costo = ?, precio_venta = ?, stock_minimo = ? WHERE id = ?";
    $sentencia = $conexion->prepare($sql);
    return $sentencia->execute([$codigo, $nombre, $descripcion, $categoria_id, $precio_costo, $precio_venta, $stock_minimo, $id]);
}

function eliminar($sentencia, $id)
{
    $bd = conectarBaseDatos();
    $respuesta = $bd->prepare($sentencia);
    return $respuesta->execute([$id]);
}
function eliminarProducto($id)
{
    $sentencia = "DELETE FROM producto WHERE id = ?";
    return eliminar($sentencia, $id);
}
function obtenerUsuarios()
{
    $sentencia = "SELECT u.id, u.nombre, u.apellido, u.tel, u.direccion, u.email, r.nombre as rol FROM usuario u
    INNER JOIN rol r ON u.rol_id = r.id";
    return select($sentencia);
}

function registrarUsuario($nombre, $apellido, $telefono, $direccion, $email, $password)
{
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $sentencia = "INSERT INTO usuario (nombre, apellido, tel, direccion, email, pass) VALUES (?,?,?,?,?,?)";
    $parametros = [$nombre, $apellido, $telefono, $direccion, $email, $passwordHash];
    return insertar($sentencia, $parametros);
}
function obtenerUsuarioPorId($id)
{
    $sentencia = "SELECT id, nombre, apellido, tel, direccion, email FROM usuario WHERE id = ?";
    return select($sentencia, [$id])[0];
}
function editarUsuario($nombre, $apellido, $telefono, $direccion, $email, $password, $id)
{
    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sentencia = "UPDATE usuario SET nombre = ?, apellido = ?, tel = ?, direccion = ?, email = ?, `pass` = ? WHERE id = ?";
        $parametros = [$nombre, $apellido, $telefono, $direccion, $email, $passwordHash, $id];
    } else {
        $sentencia = "UPDATE usuario SET nombre = ?, apellido = ?, tel = ?, direccion = ?, email = ? WHERE id = ?";
        $parametros = [$nombre, $apellido, $telefono, $direccion, $email, $id];
    }

    return editar($sentencia, $parametros);
}

function eliminarUsuario($id)
{
    $sentencia = "DELETE FROM usuario WHERE id = ?";
    return eliminar($sentencia, $id);
}
function obtenerClientes()
{
    $sentencia = "SELECT c.id, c.nombre, c.apellido, c.telefono, c.direccion, c.fechaNacimiento, c.email, c.cuil_cuit, cc.nombre AS categoria
                FROM cliente AS c
                LEFT JOIN categoria_cliente AS cc ON c.categoria_cliente_id = cc.id";
    return select($sentencia);
}
function registrarCliente($nombre, $apellido, $direccion, $fecha_nacimiento, $email, $telefono, $cuil_cuit, $dni, $categoria, $password)
{
    $sentencia = "INSERT INTO cliente (nombre, apellido, direccion, fechaNacimiento, email, telefono, cuil_cuit, dni, categoria_cliente_id, pass) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $parametros = [$nombre, $apellido, $direccion, $fecha_nacimiento, $email, $telefono, $cuil_cuit, $dni, $categoria, $password];
    return insertar($sentencia, $parametros);
}
function obtenerCategoriasCliente()
{
    $sentencia = "SELECT id, nombre FROM categoria_cliente ORDER BY nombre";
    $parametros = [];
    return select($sentencia, $parametros);
}
function obtenerClientePorId($id)
{
    $sentencia = "SELECT * FROM cliente WHERE id = ?";
    $cliente = select($sentencia, [$id]);
    if ($cliente)
        return $cliente[0];
}


// Funciones para compras
function obtenerCompraPorId($idCompra)
{
    $sentencia = "SELECT compra.*, proveedor.nombre AS proveedor FROM compra
                 LEFT JOIN proveedor ON proveedor.id = compra.proveedor_id
                 WHERE compra.id = ?";
    $parametros = [$idCompra];
    $compras = select($sentencia, $parametros);

    if (!empty($compras)) {
        return $compras[0];
    } else {
        return null; // Devolver nulo si no se encuentra la compra
    }
}

// function obtenerProveedorPorId($id)
// {
//     $sentencia = "SELECT * FROM proveedor WHERE id = ?";
//     $proveedor = select($sentencia, [$id]);
//     if ($proveedor) return $proveedor[0];
// }

function obtenerCategoriasProveedor()
{
    $sentencia = "SELECT id, nombre FROM categoria ORDER BY nombre";
    $parametros = [];
    return select($sentencia, $parametros);
}


function editarCliente($id, $nombre, $apellido, $direccion, $fecha_nacimiento, $email, $telefono, $cuil_cuit, $dni, $categoria, $password)
{
    $sentencia = "UPDATE cliente SET nombre = ?, apellido = ?, direccion = ?, fechaNacimiento = ?, email = ?, telefono = ?, cuil_cuit = ?, dni = ?, categoria_cliente_id = ?, `pass` = ? WHERE id = ?";
    $parametros = [$id, $nombre, $apellido, $direccion, $fecha_nacimiento, $email, $telefono, $cuil_cuit, $dni, $categoria, $password];
    return editar($sentencia, $parametros);
}
function eliminarCliente($id)
{
    $sentencia = "DELETE FROM cliente WHERE id = ?";
    return eliminar($sentencia, $id);
}
function calcularTotalLista($lista)
{
    $total = 0;
    foreach ($lista as $producto) {
        $total += floatval($producto->precio_venta * $producto->cantidad);
    }
    return $total;
}
function iniciarSesion($usuario, $password)
{
    $sentencia = "SELECT id, nombre, `pass` FROM usuario WHERE nombre = ?";
    $resultado = select($sentencia, [$usuario]);
    if ($resultado) {
        $usuario = $resultado[0];
        $verificaPass = verificarPassword($usuario->id, $password);
        if ($verificaPass)
            return $usuario;
    }
}
function verificarPassword($idUsuario, $password)
{
    $sentencia = "SELECT `pass` FROM usuario WHERE id = ?";
    $contrasenia = select($sentencia, [$idUsuario])[0]->pass;
    $verifica = password_verify($password, $contrasenia);
    if ($verifica)
        return true;
}
function cambiarPassword($idUsuario, $password)
{
    $nueva = password_hash($password, PASSWORD_DEFAULT);
    $sentencia = "UPDATE usuario SET pass = ? WHERE id = ?";
    return editar($sentencia, [$nueva, $idUsuario]);
}

function obtenerProductoPorCodigo($codigo)
{
    $sentencia = "SELECT * FROM producto WHERE codigo = ?";
    $producto = select($sentencia, [$codigo]);
    if ($producto)
        return $producto[0];
    return [];
}
function agregarProductoALista($producto, $listaProductos)
{
    if ($producto->stock < 1)
        return $listaProductos;

    $existe = verificarSiEstaEnLista($producto->id, $listaProductos);

    if (!$existe) {
        $producto->cantidad = 1;
        array_push($listaProductos, $producto);
    } else {
        $listaProductos = incrementarCantidad($producto->id, $listaProductos);
    }

    return $listaProductos;
}
function incrementarCantidad($productoId, $listaProductos)
{
    foreach ($listaProductos as &$producto) {
        if ($producto->id === $productoId) {
            $producto->cantidad++;
            break;
        }
    }
    return $listaProductos;
}

function verificarExistencia($idProducto, $listaProductos, $existencia)
{
    foreach ($listaProductos as $producto) {
        if ($producto->id == $idProducto) {
            if ($existencia <= $producto->cantidad)
                return true;
        }
    }
    return false;
}
function verificarSiEstaEnLista($idProducto, $listaProductos)
{
    foreach ($listaProductos as $producto) {
        if ($producto->id == $idProducto) {
            return true;
        }
    }
    return false;
}
function agregarCantidad($idProducto, $listaProductos)
{
    foreach ($listaProductos as $producto) {
        if ($producto->id == $idProducto) {
            $producto->cantidad++;
        }
    }
    return $listaProductos;
}
function registrarVenta($idUsuario, $idCliente, $total, $medioPago, $iva)
{
    $sentencia = "INSERT INTO venta (fecha, medioPago_id, cliente_id, iva_id, usuario_id, total) VALUES (NOW(), ?, ?, ?, ?, ?)";
    $parametros = [$medioPago, $idCliente, $iva, $idUsuario, $total];
    if (insertar($sentencia, $parametros)) {
        return obtenerUltimoIdVenta();
    }
    return false;
}

function obtenerVentaPorId($idVenta)
{
    $sentencia = "SELECT venta.*, IFNULL(cliente.nombre, 'MOSTRADOR') AS cliente FROM venta
                 LEFT JOIN cliente ON cliente.id = venta.cliente_id
                 WHERE venta.id = ?";
    $parametros = [$idVenta];
    $ventas = select($sentencia, $parametros);

    if (!empty($ventas)) {
        return $ventas[0];
    } else {
        return null; // Devolver nulo si no se encuentra la venta
    }
}

function actualizarStockProductos($productos)
{
    foreach ($productos as $producto) {
        $sentencia = "UPDATE producto SET stock = stock - ? WHERE id = ?";
        $parametros = [$producto->cantidad, $producto->id];
        editar($sentencia, $parametros);
    }
}

function registrarProductosVenta($productos, $idVenta)
{
    $sentencia = "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    foreach ($productos as $producto) {
        $parametros = [$idVenta, $producto->id, $producto->cantidad, $producto->precio_venta];
        insertar($sentencia, $parametros);
        descontarProductos($producto->id, $producto->cantidad);
    }
    return true;
}
function descontarProductos($idProducto, $cantidad)
{
    $sentencia = "UPDATE producto SET stock = stock - ? WHERE id = ?";
    $parametros = [$cantidad, $idProducto];
    return editar($sentencia, $parametros);
}
function obtenerUltimoIdVenta()
{
    $sentencia = "SELECT id FROM venta ORDER BY id DESC LIMIT 1";
    $resultado = select($sentencia);
    if ($resultado) {
        return $resultado[0]->id;
    } else {
        return null;
    }
}
function obtenerMediosDePago()
{
    $sentencia = "SELECT id, nombre FROM mediopago";
    return select($sentencia);
}
function obtenerIvas()
{
    $sentencia = "SELECT id, nombre FROM iva";
    return select($sentencia);
}
function obtenerVentas($fechaInicio, $fechaFin, $cliente, $usuario)
{
    $parametros = [];
    $sentencia = "SELECT venta.*, usuario.nombre, IFNULL(cliente.nombre, 'MOSTRADOR') AS cliente
    FROM venta
    INNER JOIN usuario ON usuario.id = venta.usuario_id
    LEFT JOIN cliente ON cliente.id = venta.cliente_id
    WHERE 1 = 1"; // Inicio de la consulta

    if (isset($fechaInicio) && isset($fechaFin)) {
        $sentencia .= " AND DATE(venta.fecha) BETWEEN ? AND ?";
        array_push($parametros, $fechaInicio, $fechaFin);
    }

    if (isset($cliente)) {
        $sentencia .= " AND venta.cliente_id = ?";
        array_push($parametros, $cliente);
    }

    if (isset($usuario)) {
        $sentencia .= " AND venta.usuario_id = ?";
        array_push($parametros, $usuario);
    }

    $ventas = select($sentencia, $parametros);

    return agregarProductosVendidos($ventas);
}



function agregarProductosVendidos($ventas)
{
    foreach ($ventas as $venta) {
        $venta->productos = obtenerProductosVendidos($venta->id);
    }
    return $ventas;
}
function calcularTotalVentas($ventas)
{
    $total = 0;
    foreach ($ventas as $venta) {
        $total += $venta->total;
    }
    return $total;
}

function calcularProductosVendidos($ventas)
{
    $total = 0;
    foreach ($ventas as $venta) {
        foreach ($venta->productos as $producto) {
            $total += $producto->cantidad;
        }
    }
    return $total;
}

function obtenerGananciaVentas($ventas)
{
    $total = 0;
    foreach ($ventas as $venta) {
        foreach ($venta->productos as $producto) {
            $total += $producto->cantidad * ($producto->precio_unitario - $producto->precio_costo);
        }
    }
    return $total;
}

function actualizarProducto($id, $stock, $precio_costo, $precio_venta)
{
    $sentencia = "UPDATE producto SET stock = ?, precio_costo = ?, precio_venta = ? WHERE id = ?";
    $parametros = [$stock, $precio_costo, $precio_venta, $id];
    return editar($sentencia, $parametros);
}


function obtenerProductosVendidos($idVenta)
{
    $sentencia = "SELECT detalle_venta.cantidad, detalle_venta.precio_unitario, producto.nombre,
    producto.precio_costo
    FROM detalle_venta
    INNER JOIN producto ON producto.id = detalle_venta.producto_id
    WHERE venta_id  = ? ";
    return select($sentencia, [$idVenta]);
}
function obtenerProductosComprados($idCompra)
{
    $sentencia = "SELECT detalle_compra.cantidad, detalle_compra.precio_unitario, producto.nombre,
    producto.precio_costo
    FROM detalle_compra
    INNER JOIN producto ON producto.id = detalle_compra.producto_id
    WHERE compra_id  = ?";
    return select($sentencia, [$idCompra]);
}

function registrarCompra($codigo, $cantidad, $precio_compra, $precio_venta, $idProducto, $proveedor_id, $totalCompra)
{
    try {
        $conexion = conectarBaseDatos();
        $conexion->beginTransaction();

        // Registro de compra
        $stmt = $conexion->prepare("INSERT INTO compra (fecha, proveedor_id, total) VALUES (NOW(), :proveedor_id, :totalCompra)");
        $stmt->bindParam(':proveedor_id', $proveedor_id, PDO::PARAM_INT);
        $stmt->bindParam(':totalCompra', $totalCompra, PDO::PARAM_INT);
        $stmt->execute();

        $compra_id = $conexion->lastInsertId();

        // Registro de detalles de compra
        $stmt = $conexion->prepare("INSERT INTO detalle_compra (compra_id, producto_id, cantidad, precio_unitario) VALUES (:compra_id, :producto_id, :cantidad, :precio_unitario)");
        $stmt->bindParam(':compra_id', $compra_id, PDO::PARAM_INT);
        $stmt->bindParam(':producto_id', $idProducto, PDO::PARAM_INT);
        $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(':precio_unitario', $precio_compra, PDO::PARAM_STR);
        $stmt->execute();

        // Cálculo de nuevo stock y precios
        $producto = obtenerProductoPorId($idProducto);
        $nuevo_stock = $producto->stock + $cantidad;
        $precio_costo = $precio_compra;


        // Actualización de stock y precios del producto
        $stmt = $conexion->prepare("UPDATE producto SET stock = :stock, precio_costo = :precio_costo, precio_venta = :precio_venta WHERE id = :id");
        $stmt->execute(array(
            ':stock' => $nuevo_stock,
            ':precio_costo' => $precio_costo,
            ':precio_venta' => $precio_venta,
            ':id' => $idProducto
        ));

        $conexion->commit();
        return true;
    } catch (PDOException $e) {
        if ($conexion) {
            $conexion->rollBack();
        }
        echo "Error al registrar la compra: " . $e->getMessage();
        return false;
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}

function calcularTotalCompra($compra_id)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("SELECT SUM(precio_unitario * cantidad) AS total FROM detalle_compra WHERE compra_id = :compra_id");
        $stmt->execute(array(':compra_id' => $compra_id));
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($resultado['total']) ? $resultado['total'] : 0;
    } catch (PDOException $e) {
        echo "Error al calcular el total de la compra: " . $e->getMessage();
        return 0;
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}

function actualizarTotalCompra($compra_id, $totalCompra)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("UPDATE compra SET total = :totalCompra WHERE id = :compra_id");
        $stmt->execute(array(':totalCompra' => $totalCompra, ':compra_id' => $compra_id));
    } catch (PDOException $e) {
        echo "Error al actualizar el total de la compra: " . $e->getMessage();
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}


function obtenerPrecioCompraPorProducto($productoId)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("SELECT COALESCE(precio_unitario, 0) AS precio_compra
                                    FROM detalle_compra
                                    WHERE producto_id = :producto_id
                                    ORDER BY compra_id DESC
                                    LIMIT 1");
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_OBJ);

        return isset($resultado->precio_compra) ? $resultado->precio_compra : 0;
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return 0;
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}

function obtenerUltimoIdCompra()
{
    try {
        $conexion = conectarBaseDatos();
        $query = "SELECT MAX(id) AS ultimo_id FROM compra";
        $resultado = $conexion->query($query);
        if ($resultado) {
            $fila = $resultado->fetch(PDO::FETCH_ASSOC);
            return $fila['ultimo_id'];
        } else {
            return 0; // No se encontraron compras
        }
    } catch (PDOException $e) {
        // Manejo de errores
        echo "Error al obtener el último ID de compra: " . $e->getMessage();
        return 0;
    }
}

function registrarMovimientoProducto($producto_id, $tipo, $compraId, $venta_id, $cantidad, $fecha)
{
    try {
        $conexion = conectarBaseDatos();
        $query = "INSERT INTO movimiento_producto (producto_id, tipo, compra_id, venta_id, cantidad, fecha) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($query);
        $stmt->execute([$producto_id, $tipo, $compraId, $venta_id, $cantidad, $fecha]);
        return true;
    } catch (PDOException $e) {
        echo "Error al registrar el movimiento del producto: " . $e->getMessage();
        return false;
    }
}

function obtenerSaldoCaja()
{
    try {
        $conexion = conectarBaseDatos();

        // Suma de todas las entradas
        $stmtEntradas = $conexion->query("SELECT SUM(monto) as totalEntradas FROM efectivocaja WHERE tipo = 1");
        $totalEntradas = $stmtEntradas->fetch(PDO::FETCH_ASSOC)['totalEntradas'];

        // Suma de todas las salidas
        $stmtSalidas = $conexion->query("SELECT SUM(monto) as totalSalidas FROM efectivocaja WHERE tipo = 2");
        $totalSalidas = $stmtSalidas->fetch(PDO::FETCH_ASSOC)['totalSalidas'];

        // Saldo total (entradas - salidas)
        $saldo = $totalEntradas - $totalSalidas;

        return $saldo;
    } catch (PDOException $e) {
        return 0;
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}

function obtenerHistorialCaja()
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->query("SELECT fecha, descripcion, monto FROM efectivocaja ORDER BY fecha DESC");
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $historial;
    } catch (PDOException $e) {

        return array();
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}
// function obtenerProveedores() {
//     try {
//         $conexion = conectarBaseDatos();
//         $stmt = $conexion->query("SELECT * FROM proveedor");
//         $proveedores = $stmt->fetchAll(PDO::FETCH_OBJ);

//         return $proveedores;
//     } catch (PDOException $e) {
//         echo "Error en la consulta de proveedores: " . $e->getMessage();
//         return array();
//     } finally {
//         if ($conexion) {
//             $conexion = null;
//         }
//     }
// }

function registrarEfectivoCaja($fecha, $monto, $descripcion, $entradaSalidaId, $ventaId, $tipo, $compraId)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("INSERT INTO efectivocaja (fecha, monto, descripcion, entrada_salida_id, venta_id, tipo, compra_id) VALUES (:fecha, :monto, :descripcion, :entrada_salida_id, :venta_id, :tipo, :compra_id)");
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->bindParam(':monto', $monto, PDO::PARAM_INT);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':entrada_salida_id', $entradaSalidaId, PDO::PARAM_INT);
        $stmt->bindParam(':venta_id', $ventaId, PDO::PARAM_INT);
        $stmt->bindParam(':tipo', $tipo, PDO::PARAM_INT);
        $stmt->bindParam(':compra_id', $compraId, PDO::PARAM_INT);

        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "Error al registrar efectivo en caja: " . $e->getMessage();
        return false;
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}
/**
 * Función para registrar movimientos manuales en la caja, incluyendo el saldo inicial.
 */
/**
 * Función para registrar movimientos manuales en la caja, incluyendo el saldo inicial.
 */
function registrarMovimientoManualCaja($fecha, $monto, $descripcion, $tipoMovimiento, $tipoTransaccion)
{
    $conexion = conectarBaseDatos();

    // Determina el valor de 'tipo' basado en el tipo de movimiento. 
    // Aquí asumimos '1' para entradas manuales y '2' para salidas manuales, ajusta según tu lógica.
    $tipo = $tipoMovimiento === 'Entrada' ? 1 : 2;

    // Insertar el movimiento en la tabla efectivocaja, incluyendo el campo 'tipo'
    $sql = "INSERT INTO efectivocaja (fecha, monto, descripcion, tipo_movimiento, tipo_transaccion, entrada_salida_id, venta_id, compra_id, tipo) 
            VALUES (?, ?, ?, ?, ?, NULL, NULL, NULL, ?)";

    try {
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$fecha, $monto, $descripcion, $tipoMovimiento, $tipoTransaccion, $tipo]);
        echo '<div class="alert alert-success mt-3" role="alert">Movimiento registrado con éxito.</div>';
    } catch (PDOException $e) {
        echo "Error al registrar movimiento en caja: " . $e->getMessage();
    }
}


function obtenerSaldoAnterior($fechaInicio)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("SELECT SUM(monto) as saldo_anterior FROM efectivocaja WHERE fecha < :fechaInicio");
        $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['saldo_anterior'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}

function obtenerTotalCompras($fechaInicio, $fechaFin)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("SELECT SUM(monto) as total_compras FROM efectivocaja WHERE fecha BETWEEN :fechaInicio AND :fechaFin AND tipo = 2");
        $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total_compras'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}

function obtenerTotalVentasCaja($fechaInicio, $fechaFin)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("SELECT SUM(monto) as total_ventas FROM efectivocaja WHERE fecha BETWEEN :fechaInicio AND :fechaFin AND tipo = 1");
        $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total_ventas'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}

function obtenerSaldoActual($fechaInicio, $fechaFin)
{
    $totalCompras = obtenerTotalCompras($fechaInicio, $fechaFin);
    $totalVentas = obtenerTotalVentasCaja($fechaInicio, $fechaFin);

    return $totalVentas - $totalCompras;
}

function obtenerMovimientosPorProducto($productoId)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("
            SELECT 
                mp.fecha, 
                mp.tipo, 
                mp.cantidad, 
                dc.precio_unitario AS precio_compra, 
                dv.precio_unitario AS precio_venta
            FROM movimiento_producto mp
            LEFT JOIN detalle_compra dc 
                ON mp.compra_id = dc.compra_id 
                   AND mp.producto_id = dc.producto_id
            LEFT JOIN detalle_venta dv 
                ON mp.venta_id = dv.venta_id 
                   AND mp.producto_id = dv.producto_id
            WHERE mp.producto_id = :producto_id
            ORDER BY mp.fecha DESC
        ");
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->execute();

        $movimientos = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $movimientos;
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return array();
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}


function obtenerHistorialCajaPorFecha($fechaInicio, $fechaFin)
{
    try {
        $conexion = conectarBaseDatos();

        // Ajusta la consulta para incluir venta_id y compra_id
        $sql = "SELECT fecha, descripcion, monto, venta_id, compra_id FROM efectivocaja";

        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $sql .= " WHERE DATE(fecha) BETWEEN :fechaInicio AND :fechaFin";
        } elseif (empty($fechaInicio) && !empty($fechaFin)) {
            $sql .= " WHERE DATE(fecha) <= :fechaFin";
        } elseif (!empty($fechaInicio) && empty($fechaFin)) {
            $sql .= " WHERE DATE(fecha) >= :fechaInicio";
        }

        $sql .= " ORDER BY fecha DESC";

        $stmt = $conexion->prepare($sql);

        // Bind parameters solo si se proporcionan las fechas
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
        } elseif (empty($fechaInicio) && !empty($fechaFin)) {
            $stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
        } elseif (!empty($fechaInicio) && empty($fechaFin)) {
            $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
        }

        $stmt->execute();
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $historial;
    } catch (PDOException $e) {
        return array();
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}


// function obtenerHistorialCajaPorFecha($fechaInicio, $fechaFin)
// {
//     try {
//         $conexion = conectarBaseDatos();

//         // Ajusta la consulta para filtrar solo si se proporcionan las fechas
//         $sql = "SELECT fecha, descripcion, monto FROM efectivocaja";

//         if (!empty($fechaInicio) && !empty($fechaFin)) {
//             $sql .= " WHERE DATE(fecha) BETWEEN :fechaInicio AND :fechaFin";
//         } elseif (empty($fechaInicio) && !empty($fechaFin)) {
//             $sql .= " WHERE DATE(fecha) <= :fechaFin";
//         } elseif (!empty($fechaInicio) && empty($fechaFin)) {
//             $sql .= " WHERE DATE(fecha) >= :fechaInicio";
//         }

//         $sql .= " ORDER BY fecha DESC";

//         $stmt = $conexion->prepare($sql);

//         // Bind parameters solo si se proporcionan las fechas
//         if (!empty($fechaInicio) && !empty($fechaFin)) {
//             $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
//             $stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
//         } elseif (empty($fechaInicio) && !empty($fechaFin)) {
//             $stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
//         } elseif (!empty($fechaInicio) && empty($fechaFin)) {
//             $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
//         }

//         $stmt->execute();
//         $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
//         return $historial;
//     } catch (PDOException $e) {
//         return array();
//     } finally {
//         if ($conexion) {
//             $conexion = null;
//         }
//     }
// }

function obtenerMovimientosPorProductos()
{
    // Obtener la lista de productos
    $productos = obtenerProductos();

    $movimientos = [];

    foreach ($productos as $producto) {
        $movimientoProducto = new stdClass();
        $movimientoProducto->nombre = $producto->nombre;
        $movimientoProducto->id = $producto->id;

        // Obtener detalles de ventas por mes
        $detallesVenta = obtenerDetallesVenta($producto->id);
        foreach ($detallesVenta as $detalle) {
            $mes = obtenerMesDesdeFecha($detalle->fecha); // Supongamos que tienes una función para obtener el mes desde la fecha
            $movimientoProducto->ventas[$mes][] = $detalle;
        }

        // Obtener detalles de compras por mes
        $detallesCompra = obtenerDetallesCompra($producto->id);
        foreach ($detallesCompra as $detalle) {
            $mes = obtenerMesDesdeFecha($detalle->fecha); // Supongamos que tienes una función para obtener el mes desde la fecha
            $movimientoProducto->compras[$mes][] = $detalle;
        }

        $movimientos[] = $movimientoProducto;
    }

    return $movimientos;
}

function obtenerMesDesdeFecha($fecha)
{
    try {
        $dateTime = new DateTime($fecha);
        $mes = $dateTime->format('n'); // 'n' devuelve el número del mes sin ceros iniciales (1 hasta 12)
        return $mes;
    } catch (Exception $e) {
        // Manejar el error según tus necesidades
        echo "Error al obtener el mes desde la fecha: " . $e->getMessage();
        return 0; // En caso de error, devolver 0 o manejar de otra manera
    }
}

function obtenerDetallesVenta($productoId)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("SELECT * FROM detalle_venta WHERE producto_id = :producto_id");
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->execute();
        $detallesVenta = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $detallesVenta;
    } catch (PDOException $e) {
        // Manejar el error según tus necesidades
        echo "Error al obtener detalles de venta: " . $e->getMessage();
        return [];
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}

// Función para obtener detalles de compra por producto
function obtenerDetallesCompra($productoId)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("SELECT * FROM detalle_compra WHERE producto_id = :producto_id");
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->execute();
        $detallesCompra = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $detallesCompra;
    } catch (PDOException $e) {
        // Manejar el error según tus necesidades
        echo "Error al obtener detalles de compra: " . $e->getMessage();
        return [];
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}

function obtenerMovimientosPorMes($idProducto)
{
    // Obtener productos vendidos por mes
    $ventas = select("SELECT DATE_FORMAT(venta.fecha, '%Y-%m') AS mes, SUM(detalle_venta.cantidad) AS cantidad
                      FROM venta
                      INNER JOIN detalle_venta ON venta.id = detalle_venta.venta_id
                      WHERE detalle_venta.producto_id = ?
                      GROUP BY mes", [$idProducto]);

    // Obtener productos comprados por mes
    $compras = select("SELECT DATE_FORMAT(compra.fecha, '%Y-%m') AS mes, SUM(detalle_compra.cantidad) AS cantidad
                       FROM compra
                       INNER JOIN detalle_compra ON compra.id = detalle_compra.compra_id
                       WHERE detalle_compra.producto_id = ?
                       GROUP BY mes", [$idProducto]);

    // Unir la información de ventas y compras
    $movimientos = array_merge($ventas, $compras);

    return $movimientos;
}
function obtenerNombreMes($numeroMes)
{
    $meses = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    return $meses[$numeroMes] ?? 'Desconocido';
}



function obtenerStockProducto($productoId)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("SELECT stock FROM producto WHERE id = :producto_id");
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['stock'];
    } catch (PDOException $e) {
        return 0;
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}


function registrarTransaccionCaja($fecha, $monto, $descripcion, $entradaSalidaId, $ventaId, $tipo, $compraId)
{
    try {
        $conexion = conectarBaseDatos();
        $stmt = $conexion->prepare("INSERT INTO efectivocaja (fecha, monto, descripcion, entrada_salida_id, venta_id, tipo, compra_id) VALUES (:fecha, :monto, :descripcion, :entrada_salida_id, :venta_id, :tipo, :compra_id)");
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->bindParam(':monto', $monto, PDO::PARAM_INT);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':entrada_salida_id', $entradaSalidaId, PDO::PARAM_INT);
        $stmt->bindParam(':venta_id', $ventaId, PDO::PARAM_INT);
        $stmt->bindParam(':tipo', $tipo, PDO::PARAM_INT);
        $stmt->bindParam(':compra_id', $compraId, PDO::PARAM_INT);

        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "Error al registrar efectivo en caja: " . $e->getMessage();
        return false;
    } finally {
        if ($conexion) {
            $conexion = null;
        }
    }
}


function conectarBaseDatos()
{
    // Credenciales LOCALES (Laragon)
    $host = "localhost";
    $db = "drinkstore_db";
    $user = "root";
    $pass = "";
    $charset = 'utf8mb4';

    $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    try {
        $pdo = new \PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int) $e->getCode());
    }
}
