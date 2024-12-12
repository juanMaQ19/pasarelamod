<?php
require_once '../config/config.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    if ($action == 'agregar') {
        $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : 0;
        $respuesta = agregar($id, $cantidad);
        if ($respuesta > 0) {
            $_SESSION['carrito']['productos'][$id] = $cantidad;
            $datos['ok'] = true;
        } else {
            $datos['ok'] = false;
            $datos['cantidadAnterior'] = $_SESSION['carrito']['productos'][$id];
        }
        $datos['sub'] = MONEDA . number_format($respuesta, 2, '.', '');
    } elseif ($action == 'eliminar') {
        $datos['ok'] = eliminar($id);
    } else {
        $datos['ok'] = false;
    }
} else {
    $datos['ok'] = false;
}
echo json_encode($datos);

function agregar($id, $cantidad)
{
    if (
        $id > 0 && $cantidad > 0 && is_numeric(($cantidad))
        && (isset($_SESSION['carrito']['productos'][$id]))
    ) {    
        $db = new Database();
        $con = $db->conectar();
        $sql = $con->prepare("SELECT precio, descuento, stock FROM productos WHERE id = ? AND activo = 1 LIMIT 1");
        $sql->execute([$id]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        $precio = $row['precio'];
        $descuento = $row['descuento'];
        $stock = $row['stock'];

        if ($stock >= $cantidad) {
            $precio_desc = $precio - (($precio * $descuento) / 100);
            return $cantidad * $precio_desc;
        }
    }
    return 0;
}

function eliminar($id)
{
    if ($id > 0) {
        if (isset($_SESSION['carrito']['productos'][$id])) {
            unset($_SESSION['carrito']['productos'][$id]);
            return true;
        }
    } else {
        return false;
    }
}
