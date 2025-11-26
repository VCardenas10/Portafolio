<?php
session_start();
include_once('../DB/db.php');

if (isset($_SESSION['rut_persona'])) {
    $rut = $_SESSION['rut_persona'];

    $sql = "SELECT nombre FROM persona WHERE rut_persona = :rut";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':rut', $rut);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $nombre = $resultado['nombre'];
    }
}


if (!isset($_GET['ID_art']) || !isset($_SESSION['rut_persona'])) {
    echo "Parámetros inválidos.";
    exit;
}

$id_art = $_GET['ID_art'];
$rut_revisor = $_SESSION['rut_persona'];

// Obtener título del artículo
$sql = "SELECT titulo FROM articulo WHERE ID_art = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id_art);
$stmt->execute();
$articulo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$articulo) {
    echo "Artículo no encontrado.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['calidad_tecnica'], $_POST['originalidad'], $_POST['valoracion_global'],
          $_POST['comentarios'], $_POST['argumentos'])) {

    $calidad_tecnica = (int)$_POST['calidad_tecnica'];
    $originalidad = (int)$_POST['originalidad'];
    $valoracion_global = (int)$_POST['valoracion_global'];

    $comentarios = trim($_POST['comentarios']);
    $argumentos = trim($_POST['argumentos']);

    // Calcular promedio
    $calificacion = round(($calidad_tecnica + $originalidad + $valoracion_global) / 3, 2); // redondeado a 2 decimales

    // Actualizar la tabla art_revisor
    $update = "UPDATE art_revisor 
               SET Calificacion = :calificacion,
                   Comentarios = :comentarios,
                   Argumentos = :argumentos,
                   Revisado = 1
               WHERE ID_art = :id_art AND rut_revisor = :rut_revisor";

    $stmt = $pdo->prepare($update);
    $stmt->bindParam(':calificacion', $calificacion);
    $stmt->bindParam(':comentarios', $comentarios);
    $stmt->bindParam(':argumentos', $argumentos);
    $stmt->bindParam(':id_art', $id_art);
    $stmt->bindParam(':rut_revisor', $rut_revisor);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Artículo evaluado exitosamente.</p>";
        echo "<a href='dashboard_revisores.php'>Volver al dashboard</a>";
        exit;
    } else {
        echo "<p style='color: red;'>Error al guardar la evaluación.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Tarea 2</title>

    <!-- Custom fonts for this template-->
    <link href="includes/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">


    <!-- Custom styles for this page -->
    <link href="includes/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include("includes/slidebar_revisor.php") ?> 
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include("includes/header.php") ?>
                <!-- End of Topbar -->
    
                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h4 class="m-0 font-weight-bold text-primary">Formulario de Evaluación</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="calidad_tecnica">Calidad Técnica:</label>
                                    <select class="form-control" id="calidad_tecnica" name="calidad_tecnica" required>
                                        <option value="">-- Seleccionar --</option>
                                        <?php for ($i = 1; $i <= 7; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="originalidad">Originalidad:</label>
                                    <select class="form-control" id="originalidad" name="originalidad" required>
                                        <option value="">-- Seleccionar --</option>
                                        <?php for ($i = 1; $i <= 7; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="valoracion_global">Valoración Global:</label>
                                    <select class="form-control" id="valoracion_global" name="valoracion_global" required>
                                        <option value="">-- Seleccionar --</option>
                                        <?php for ($i = 1; $i <= 7; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="argumentos">Argumentos Valoración Global:</label>
                                    <textarea class="form-control" id="argumentos" name="argumentos" rows="3" required></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="comentarios">Comentarios a Autores:</label>
                                    <textarea class="form-control" id="comentarios" name="comentarios" rows="3" required></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">Enviar Evaluación</button>
                                <a href="dashboard_revisores.php" class="btn btn-secondary">Volver</a>
                            </form>
                        </div>
                    </div>
                </div>
    <!-- Footer -->
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Tarea 2 &copy; Victor Cardenas; Miguel Rivero</span>
            </div>
        </div>
    </footer>
    <!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->
        

    </script>
    <!-- Bootstrap core JavaScript-->
    <script src="includes/vendor/jquery/jquery.min.js"></script>
    <script src="includes/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="includes/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="assets/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="includes/vendor/chart.js/Chart.min.js"></script>
    <script src="includes/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="includes/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="assets/js/demo/chart-area-demo.js"></script>
    <script src="assets/js/demo/chart-pie-demo.js"></script>
    <script src="assets/js/demo/datatables-demo.js"></script>



</body>

</html>

