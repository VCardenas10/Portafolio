<?php
    include_once('../DB/db.php');

$topicos = $pdo->query("SELECT topico FROM topico_especialidad")->fetchAll(PDO::FETCH_ASSOC);
?>


<div class="modal fade" id="modalNuevoRevisor" tabindex="-1" role="dialog" aria-labelledby="modalNuevoRevisorLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formNuevoRevisor" action="save_revisor.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Nuevo Revisor</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre</label>
            <input type="text" class="form-control" name="nombre" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="form-group">
            <label>RUT</label>
            <input type="number" class="form-control" name="rut" required>
          </div>
          <div class="form-group">
            <label>Contraseña</label>
            <input type="password" class="form-control" name="password" required>
          </div>
        </div>
        <div class="form-group">
          <label>Tópicos que puede revisar:</label><br>
          <?php foreach ($topicos as $topico): ?>
              <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="topicos[]" value="<?= htmlspecialchars($topico['topico']) ?>" id="topico_<?= htmlspecialchars($topico['topico']) ?>">
                  <label class="form-check-label" for="topico_<?= htmlspecialchars($topico['topico']) ?>">
                      <?= htmlspecialchars($topico['topico']) ?>
                  </label>
              </div>
          <?php endforeach; ?>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("formNuevoRevisor");
    form.addEventListener("submit", function(event) {
      const checkboxes = document.querySelectorAll('input[name="topicos[]"]:checked');
      if (checkboxes.length === 0) {
        alert("Debes seleccionar al menos un tópico.");
        event.preventDefault(); // Evita que se envíe el formulario
      }
    });
  });
</script>
