<?php
require_once 'config/config.php';
$db = new Database();
$con = $db->conectar();

$id_transaccion = isset($_GET['key']) ? $_GET['key'] : 0;
$error = '';

if ($id_transaccion == '') {
    $error = "Error al procesar la transaccion";
} else {
    $sql = $con->prepare("SELECT count(id) FROM compra WHERE id_transaccion = ? AND status =?");
    $sql->execute([$id_transaccion, 'Pagado']);
    if ($sql->fetchColumn() > 0) {
        $sql = $con->prepare("SELECT id, fecha, email, totalBOB FROM compra WHERE id_transaccion = ? AND status =? LIMIT 1");
        $sql->execute([$id_transaccion, 'Pagado']);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        $idCompra = $row['id'];
        $total = $row['totalBOB'];
        $fecha = $row['fecha'];
        $sqlDet = $con->prepare("SELECT nombre, precio, cantidad FROM detalle_compra WHERE id_compra = ?");
        $sqlDet->execute([$idCompra]);
    } else {
        $error = 'Error al comprobar la compra';
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="css/estilos.css">

</head>

<body>
    <?php include 'menu.php'; ?>
    <main>
        <div class="container">
            <?php if (strlen($error) > 0 || $id_transaccion == 0) { ?>
                <div class="row">
                    <div class="col">
                        <h3><?php echo $error; ?></h3>
                    </div>
                </div>
            <?php } else { ?>
                <div class="row">
                    <div class="col mx-2">
                        <b>ID de la compra: </b><?php echo $id_transaccion; ?><br>
                        <b>Fecha de la compra: </b><?php echo $fecha; ?><br>
                        <b>Total de la compra: </b><?php echo MONEDA . number_format($total, 2, '.', ''); ?><br>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <table class="table table-responsive">
                            <thead>
                                <tr>
                                    <th>Cantidad</th>
                                    <th>Producto</th>
                                    <th>Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row_det = $sqlDet->fetch(PDO::FETCH_ASSOC)) {
                                    $importe = $row_det['precio'] * $row_det['cantidad']; ?>
                                    <tr>
                                        <td><?php echo $row_det['cantidad']; ?></td>
                                        <td><?php echo $row_det['nombre']; ?></td>
                                        <td><?php echo MONEDA . $importe; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col mx-2">
                        <a href="compra_detalle.php" class="btn btn-primary">Tus compras</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </main>


</html>