<?php
ob_start(); // Inicia el buffer de salida

require_once '../config/database.php';
require_once '../config/config.php';

if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] != 'admin' && $_SESSION['user_type'] != 'ayudante')) {
    header("Location: ../index.php");
    exit;
}

if($_SESSION['user_type'] == 'ayudante'){
    require_once '../header2.php';
}else{
    require_once '../header.php';
}

$db = new Database();
$con = $db->conectar();

$nombre = $_POST['nombre'];
$descripcion = $_POST['descripcion'];
$precio = $_POST['precio'];
$descuento = $_POST['descuento'];
$stock = $_POST['stock'];
$categoria = $_POST['categoria'];

$sql = "INSERT INTO productos (nombre, descripcion, precio, descuento, stock, id_categoria, activo)
VALUES (?, ?, ?, ?, ?, ?, 1)";
$stm = $con->prepare($sql);
if ($stm->execute([$nombre, $descripcion, $precio, $descuento, $stock, $categoria])) {

    $id = $con->lastInsertId();

    if ($_FILES['imagen_principal']['error'] == UPLOAD_ERR_OK) {
        $dir = '../../images/productos/' . $id . '/';
        $permitidos = ['jpeg', 'jpg'];

        $arregloImagen = explode('.', $_FILES['imagen_principal']['name']);
        $extension = strtolower(end($arregloImagen));
        if (in_array($extension, $permitidos)) {
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $ruta_img = $dir . 'principal.' . $extension;
            if (move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $ruta_img)) {
                echo "El archivo se cargo correctamente.";
            } else {
                echo "Error al cargar el archivo";
            }
        } else {
            echo "Archivo no permitido";
        }
    } else {
        echo "No enviaste archivo";
    }

    if (isset($_FILES['otras_imagenes'])) {
        $dir = '../../images/productos/' . $id . '/';
        $permitidos = ['jpeg', 'jpg'];

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $contador = 1;

        foreach ($_FILES['otras_imagenes']['tmp_name'] as $key => $tmp_name) {
            $fileName = $_FILES['otras_imagenes']['name'][$key];

            $arregloImagen = explode('.', $fileName);
            $extension = strtolower(end($arregloImagen));

            if (in_array($extension, $permitidos)) {
                $ruta_img = $dir . $contador . '.' . $extension;
                if (move_uploaded_file($tmp_name, $ruta_img)) {
                    echo "El archivo se cargo correctamente.<br>";
                    $contador++;
                } else {
                    echo "Error al cargar el archivo";
                }
            } else {
                echo "Archivo no permitido";
            }
        }
    }
}

// Al final de todo el script, redirige a la página principal
header('Location: index.php');
exit;

ob_end_flush(); // Envía la salida al navegador
