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


    #Consulta de autores
    $cant_autores_query = "SELECT count(*) as total FROM rol_personas WHERE ROL_ID = '1' OR ROL_ID = '3'";

    $stmt = $pdo->prepare($cant_autores_query);
    $stmt->execute();

    $resultado_autores = $stmt->fetch(PDO::FETCH_ASSOC);
    $cant_autores = $resultado_autores['total'];


    #Consulta de revisores
    $cant_revisores_query = "SELECT count(*) as total FROM rol_personas WHERE ROL_ID = '2' OR ROL_ID = '3'";

    $stmt = $pdo->prepare($cant_revisores_query);
    $stmt->execute();

    $resultado_revisores = $stmt->fetch(PDO::FETCH_ASSOC);
    $cant_revisores = $resultado_revisores['total'];

    
    #Consulta de articulos revisados
    $cant_articulos_rev_query = "SELECT 
            ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM articulo)),2) AS porcentaje_completamente_revisados
        FROM (
            SELECT articulo.ID_art
            FROM art_revisor
            JOIN articulo ON articulo.ID_art = art_revisor.ID_art
            WHERE art_revisor.revisado = TRUE
            GROUP BY articulo.ID_art
            HAVING COUNT(art_revisor.rut_revisor) = 3
        ) AS subconsulta";

    $stmt = $pdo->prepare($cant_articulos_rev_query);
    $stmt->execute();


    $resultado_rev = $stmt->fetch(PDO::FETCH_ASSOC);
    $cant_art_rev = round($resultado_rev['porcentaje_completamente_revisados']);


    #Consulta de articulos sin revisar
    $cant_art_sr_query = "SELECT 
            (SELECT COUNT(*) FROM articulo) - COUNT(*) AS restantes
        FROM (
            SELECT articulo.ID_art
            FROM art_revisor
            JOIN articulo ON articulo.ID_art = art_revisor.ID_art
            WHERE art_revisor.revisado = TRUE
            GROUP BY articulo.ID_art
            HAVING COUNT(art_revisor.rut_revisor) = 3
        ) AS subconsulta";
    
    $stmt = $pdo->prepare($cant_art_sr_query);
    $stmt->execute();

    $resultado_sinrev = $stmt->fetch(PDO::FETCH_ASSOC);
    $cant_art_sinrev = $resultado_sinrev['restantes'];

    $cant_art_query = "SELECT COUNT(*) AS total FROM articulo";
    $stmt = $pdo->prepare($cant_art_query);
    $stmt->execute();

    $resultado_art = $stmt->fetch(PDO::FETCH_ASSOC);
    $cant_articulo = $resultado_art['total'];

    #Recibir los titulos
  

    /*(
        SELECT GROUP_CONCAT(DISTINCT p1.nombre SEPARATOR ', ')
        FROM (
            SELECT rut_persona FROM art_autor WHERE ID_art = a.ID_art
        ) AS autores
        JOIN persona p1 ON p1.rut_persona = autores.rut_persona
    ) AS autores,

    -- Revisores
    (
        SELECT GROUP_CONCAT(DISTINCT p2.nombre SEPARATOR ', ')
        FROM art_revisor ar
        JOIN persona p2 ON ar.rut_revisor = p2.rut_persona
        WHERE ar.ID_art = a.ID_art
    ) AS revisores,

    -- Tópicos (principales y extras)
    (
        SELECT GROUP_CONCAT(DISTINCT te.topico SEPARATOR ', ')
        FROM (
            SELECT ID_top FROM art_topico WHERE ID_art = a.ID_art
        ) AS topicos
        JOIN topico_especialidad te ON te.ID_top = topicos.ID_top
    ) AS topicos */
    $evaluaciones_query = "SELECT 
            ar.ID_art,
            ar.Calidad_tecnica,
            ar.Originalidad,
            ar.Valoracion_global,
            ar.Argumentos,
            ar.Comentarios
        FROM art_revisor ar
        WHERE ar.ID_art = :id_art
    ";

    $stmt = $pdo->prepare($evaluaciones_query);
    $stmt->bindParam(':id_art', $articulo['ID_art']);
    $stmt->execute();
    $evaluaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $titulos_query = "SELECT 
        a.ID_art, 
        a.titulo,
        a.resumen, 
        a.fecha, 
        a.calificacion,
        CASE 
            WHEN a.ID_art IN (SELECT ID_art FROM articulo_no_rev) THEN 1 
            ELSE 0 
        END AS puede_editar
    FROM articulo a
    JOIN art_autor aa ON a.ID_art = aa.ID_art
    WHERE aa.rut_persona = :rut
";

$stmt = $pdo->prepare($titulos_query);
$stmt->bindParam(':rut', $rut);
$stmt->execute();
$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);


    if (isset($_SESSION['mensaje'])): ?>
        <div id="mensaje" class="alert alert-success" role="alert" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
            <?= htmlspecialchars($_SESSION['mensaje']) ?>
        </div>
        <?php unset($_SESSION['mensaje']); // Eliminar el mensaje después de mostrarlo ?>
    <?php endif; ?>








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
        <?php include("includes/slidebar_autor.php") ?>
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

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Articulos Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Articulos</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $cant_articulo; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Revisores Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Fecha Límite</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">03-07-2025</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Sube tu Articulo button -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <a href="#" class="btn btn-primary shadow-sm h-100 w-100 d-flex justify-content-center align-items-center" 
                            data-toggle="modal" data-target="#formularioModal">
                                <i class="fas fa-download fa-sm text-white-70"></i> Sube Tu Artículo
                            </a>
                        </div>
                    </div>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Articulos Enviados</h6>
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
                                                    <th>Título</th>
                                                    <th>Fecha de publicación</th>
                                                    <th>Calificación</th>
                                                    <th>Gestión</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($articulos as $index => $articulo): ?>
                                                <tr class="<?= $clase_fila ?>">
                                                    <td><?= htmlspecialchars($articulo['ID_art']) ?></td>
                                                    <td><?= htmlspecialchars($articulo['titulo']) ?></td>
                                                    <td><?= htmlspecialchars($articulo['fecha']) ?></td>
                                                    <td><?= is_null($articulo['calificacion']) ? 'null' : htmlspecialchars($articulo['calificacion']) ?></td>
                                                    <td>
                                                        <?php if ($articulo['puede_editar'] == 1): ?>
                                                            <a href="#" data-toggle="modal" data-target="#editarArticulo<?= htmlspecialchars($articulo['ID_art']) ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="#" data-toggle="modal" data-target="#deleteModal<?= $articulo['ID_art'] ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <!-- Botón para abrir el modal -->
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

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    
    <?php foreach ($articulos as $articulo): ?>
    <?php 
    // Obtener los tópicos seleccionados para este artículo desde la base de datos.
    $sql = "SELECT topico FROM topico_especialidad 
            INNER JOIN art_topico ON topico_especialidad.ID_top = art_topico.ID_top 
            WHERE art_topico.ID_art = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$articulo['ID_art']]);
    $topicosSeleccionados = $stmt->fetchAll(PDO::FETCH_COLUMN); // Array de nombres de tópicos
    ?>

    <div class="modal fade" id="editarArticulo<?= htmlspecialchars($articulo['ID_art']) ?>" tabindex="-1" role="dialog" aria-labelledby="editarArticuloLabel<?= htmlspecialchars($articulo['ID_art']) ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="edit_articulo.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarArticuloLabel<?= htmlspecialchars($articulo['ID_art']) ?>">Editar Artículo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="ID_art" value="<?= htmlspecialchars($articulo['ID_art']) ?>">
                        <div class="form-group">
                            <label for="titulo">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($articulo['titulo']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="resumen">Resumen</label>
                            <textarea class="form-control" id="resumen" name="resumen" rows="4" required><?= htmlspecialchars($articulo['resumen'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group mt-4">
                            <label>Tópicos:</label>
                            <div class="row">
                                <?php 
                                // Lista de todos los tópicos disponibles
                                $sql = "SELECT topico FROM topico_especialidad";
                                $stmt = $pdo->query($sql);
                                $topicosDisponibles = $stmt->fetchAll(PDO::FETCH_COLUMN); 

                                // Dividir la lista en columnas
                                $columnas = array_chunk($topicosDisponibles, ceil(count($topicosDisponibles) / 3));
                                foreach ($columnas as $columna): ?>
                                    <div class="col-md-4">
                                        <?php foreach ($columna as $topico): ?>
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input" 
                                                    type="checkbox" 
                                                    name="topicos[]" 
                                                    value="<?= htmlspecialchars($topico) ?>" 
                                                    id="topico<?= htmlspecialchars($topico) ?>" 
                                                    <?= in_array($topico, $topicosSeleccionados) ? 'checked' : '' ?>
                                                >
                                                <label class="form-check-label" for="topico<?= htmlspecialchars($topico) ?>">
                                                    <?= htmlspecialchars($topico) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal de confirmación -->
 <?php foreach ($articulos as $articulo): ?>
    <div class="modal fade" id="deleteModal<?= htmlspecialchars($articulo['ID_art']) ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?= htmlspecialchars($articulo['ID_art']) ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel<?= htmlspecialchars($articulo['ID_art']) ?>">Confirmar eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar el artículo <strong><?= htmlspecialchars($articulo['titulo']) ?></strong>? 
                Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form action="delete_articulo.php" method="POST" style="display: inline;">
                    <input type="hidden" name="ID_art" value="<?= htmlspecialchars($articulo['ID_art']) ?>">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php endforeach; ?>






    <?php include("form_save_articulo.php") 
    ?>

    <!-- Bootstrap core JavaScript-->
    <script src="includes/vendor/jquery/jquery.min.js"></script>
    <script src="includes/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
     
    <script>
document.addEventListener("DOMContentLoaded", function () {
    const addAuthorButton = document.getElementById("add-author");
    const autoresSection = document.getElementById("autores-section");

    addAuthorButton.addEventListener("click", function () {
        const newAuthorTemplate = `
            <div class="form-group additional-author">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="autor_nombre[]" placeholder="Nombre del autor" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="rut_autor[]" placeholder="Rut del autor" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <input type="radio" name="autor_contacto" value="1" class="form-check-input autor-contacto" required>
                        <label class="form-check-label ml-2">Contacto</label>
                        <button type="button" class="btn btn-link btn-m p-0 ml-2 remove-author" style="text-decoration: none;">&times;</button>

                    </div>
                </div>
            </div>
        `;
        // Agregar el nuevo grupo de autor al final de la sección
        autoresSection.insertAdjacentHTML("beforeend", newAuthorTemplate);
    });

    // Delegación de eventos para manejar el botón "Eliminar"
    autoresSection.addEventListener("click", function (event) {
        if (event.target.classList.contains("remove-author")) {
            const authorGroup = event.target.closest(".additional-author");
            if (authorGroup) {
                authorGroup.remove();
            }
        }
    });
});
</script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mensaje = document.getElementById('mensaje');
            if (mensaje) {
                setTimeout(() => {
                    mensaje.style.transition = "opacity 0.5s";
                    mensaje.style.opacity = "0";
                    setTimeout(() => mensaje.remove(), 500);
                }, 3000); // Ocultar después de 3 segundos
            }
        });
        </script>
        <script>
            document.querySelectorAll('[data-target^="#deleteModal"]').forEach(button => {
            console.log(button);
        });

    </script>


                                                


</body>

</html>