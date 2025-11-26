<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form action="topbar_advncd_search.php" method="GET"
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">

                            <!-- Selector de clasificación -->
                            <select id="categoria" class="form-control" name="categoria" required>
                                <option value="" disabled selected>Filtrar por...</option>
                                <option value="autor">Autor</option>
                                <option value="fecha">Fecha de envío</option>
                                <option value="topicos">Tópicos</option>
                                <option value="revisor">Revisor</option>
                            </select>

                            <!-- Contenedor para el campo de búsqueda -->
                            <div id="input-container" class="ml-2">
                                <input type="text" name="termino" class="form-control bg-light border-0 small" 
                                    placeholder="Escribe tu búsqueda..." required>
                            </div>

                            <!-- Botón de búsqueda -->
                            <div class="input-group-append ml-2">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>


                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $nombre; ?></span>
                                <img class="img-profile rounded-circle"
                                    src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>


<!-- Script -->
<script>
    const categoriaSelect = document.getElementById('categoria');
    const inputContainer = document.getElementById('input-container');

    categoriaSelect.addEventListener('change', function () {
        inputContainer.innerHTML = '';

        let inputField;

        if (this.value === 'fecha') {
            inputField = document.createElement('input');
            inputField.type = 'date';
            inputField.name = 'termino';
            inputField.className = 'form-control bg-light border-0 small';
            inputField.required = true;
        } else {
            inputField = document.createElement('input');
            inputField.type = 'text';
            inputField.name = 'termino';
            inputField.placeholder = 'Escribe tu búsqueda...';
            inputField.className = 'form-control bg-light border-0 small';
            inputField.required = true;
        }

        inputContainer.appendChild(inputField);
    });
</script>