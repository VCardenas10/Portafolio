<?php
    include("save_revisor.php");
    include("delete_revisor.php");
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

    #Obtener personas del comite

    $Participantes_query = "SELECT persona.rut_persona, persona.nombre, persona.email,persona.password, GROUP_CONCAT(topico_especialidad.topico SEPARATOR ', ') AS topicos
    FROM persona
    JOIN topico_revisor ON topico_revisor.rut_persona = persona.rut_persona
    JOIN topico_especialidad ON topico_revisor.ID_top = topico_especialidad.id_top
    GROUP BY persona.rut_persona, persona.nombre, persona.email";
    $stmt = $pdo->prepare($Participantes_query);
    $stmt->execute();
    $resultado_revisores = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <?php include("includes/slidebar_admin.php") ?> 
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include("includes/header.php") ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Revisores</h6>
                            
                            <div class="col-auto">
                                <a href="#" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalNuevoRevisor">
                                    Añadir Revisor
                                </a>
                                <?php include('form_save_revisor.php') ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <div id="dataTable_wrapper" class="dataTables_wrapper dt-bootstrap4">
                                    <div class="row">
                                        <div class="col-sm-12">
                                        <table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Revisores</th>
                                                    <th>Email</th>
                                                    <th>Topicos</th>
                                                    <th>Gestion</th>
                                                    <th>Eliminar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($resultado_revisores as $index => $fila): ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td><?= htmlspecialchars($fila['nombre']) ?></td>
                                                        <td><?= htmlspecialchars($fila['email']) ?></td>
                                                        <td><?= htmlspecialchars($fila['topicos']) ?></td>
                                                        <td>
                                                            <!-- Botón que abre el modal -->
                                                            <a href="#" data-toggle="modal" data-target="#editarRevisor<?= $fila['rut_persona'] ?>">
                                                                Gestionar
                                                            </a>

                                                            <!-- Modal -->
                                                            <div class="modal fade" id="editarRevisor<?= $fila['rut_persona'] ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                <div class="modal-dialog" role="document">
                                                                    <div class="modal-content">
                                                                        <!-- FORMULARIO DE EDICIÓN -->
                                                                        <form action="edit_revisor.php" method="POST">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title">Editar Revisor</h5>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>

                                                                            <div class="modal-body">
                                                                                <input type="hidden" name="rut_persona" value="<?= $fila['rut_persona'] ?>">

                                                                                <div class="form-group">
                                                                                    <label>Nombre</label>
                                                                                    <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($fila['nombre']) ?>" required>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label>Email</label>
                                                                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($fila['email']) ?>" required>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label>Rut</label>
                                                                                    <input type="number" class="form-control" name="rut_mostrar" value="<?= htmlspecialchars($fila['rut_persona']) ?>" disabled>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label>Contraseña</label>
                                                                                    <input type="password" class="form-control" name="nueva_password" placeholder="Nueva contraseña (dejar en blanco si no cambia)">
                                                                                </div>
                                                                            </div>

                                                                            <div class="modal-footer d-flex justify-content-between">
                                                                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <!-- Columna para Eliminar -->
                                                        <td>
                                                            <!-- FORMULARIO DE ELIMINACIÓN -->
                                                            <form action="delete_revisor.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este revisor?');">
                                                                <input type="hidden" name="rut_persona" value="<?= $fila['rut_persona'] ?>">
                                                                <button type="submit" class="btn btn-danger btn-block">Eliminar</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

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

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

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