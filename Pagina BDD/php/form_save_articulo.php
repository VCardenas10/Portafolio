<?php
// Conectar a la base de datos
include_once('../DB/db.php');

// Suponiendo que los datos del autor están en las variables de sesión
$autor_sesion_nombre = $_SESSION['nombre'] ?? '';
$autor_sesion_rut = $_SESSION['rut_persona'] ?? '';
?>

<div class="modal fade" id="formularioModal" tabindex="-1" role="dialog" aria-labelledby="formularioModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formularioModalLabel">Subir Artículo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Tu formulario existente -->
                <form method="POST" action="save_articulo2.php">
                    <div class="form-group">
                        <label for="titulo">Título:</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>

                    <div class="form-group">
                        <label for="resumen">Resumen:</label>
                        <textarea class="form-control" id="resumen" name="resumen" rows="4" required></textarea>
                    </div>

                    <div id="autores-section">
                        <label>Autores:</label>
                        <!-- Primer autor: datos de sesión -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="autor_nombre[]" value="<?= htmlspecialchars($autor_sesion_nombre) ?>" readonly required>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="rut_autor[]" value="<?= htmlspecialchars($autor_sesion_rut) ?>" readonly required>
                                </div>
                                <div class="col-md-4 d-flex align-items-center">
                                    <input type="radio" name="autor_contacto" value="0" class="form-check-input autor-contacto" checked required>
                                    <label class="form-check-label ml-2">Contacto</label>
                                </div>
                            </div>
                        </div>

                        <!-- Plantilla para autores adicionales -->
                        
                    </div>

                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-author">Agregar otro autor</button>

                    <div class="form-group mt-4">
                        <label>Tópicos:</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Action" id="topico1">
                                    <label class="form-check-label" for="topico1">Action</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Historical" id="topico2">
                                    <label class="form-check-label" for="topico2">Historical</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Comedy" id="topico3">
                                    <label class="form-check-label" for="topico3">Comedy</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Drama" id="topico2">
                                    <label class="form-check-label" for="topico2">Drama</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Horror" id="topico3">
                                    <label class="form-check-label" for="topico3">Horror</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Sci-Fi" id="topico4">
                                    <label class="form-check-label" for="topico4">Sci-Fi</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Romance" id="topico5">
                                    <label class="form-check-label" for="topico5">Romance</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Thriller" id="topico6">
                                    <label class="form-check-label" for="topico6">Thriller</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Documentary" id="topico5">
                                    <label class="form-check-label" for="topico5">Documentary</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Animation" id="topico6">
                                    <label class="form-check-label" for="topico6">Animation</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Adventure" id="topico7">
                                    <label class="form-check-label" for="topico7">Adventure</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Fantasy" id="topico8">
                                    <label class="form-check-label" for="topico8">Fantasy</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Mystery" id="topico9">
                                    <label class="form-check-label" for="topico9">Mystery</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Crime" id="topico9">
                                    <label class="form-check-label" for="topico9">Crime</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="topicos[]" value="Musical" id="topico9">
                                    <label class="form-check-label" for="topico9">Musical</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Enviar</button>
                </form>
            </div>
        </div>
    </div>
</div>