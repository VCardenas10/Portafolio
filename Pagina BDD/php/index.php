<?php     include_once('../DB/db.php');
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--=============== REMIXICONS ===============-->
        <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">

        <!--=============== CSS ===============-->
        <link rel="stylesheet" href="assets/css/styles.css">

        <title>Tarea 2</title>
    </head>
    <body>
        <div class="container">
            <div class="login__content">
                <img src="assets/img/bg-login.png" alt="login image" class="login__img">

                <form action="login.php" method= "POST" class="login__form">
                    <div>
                        <h1 class="login__title">
                            <span>Welcome</span> Back
                        </h1>
                        <p class="login__description">
                            Welcome! Please login to continue.
                        </p>
                    </div>
                    
                    <div>
                        <div class="login__inputs">

                            <div>
                                <label for="input-rut" class="login__label">RUT</label>
                                <input type="text" placeholder="Ingresa tu RUT" required class="login__input" id="input-rut" name="rut">
                            </div>
                        
                            <div>
                                <label for="input-email" class="login__label">Email</label>
                                <input type="email" placeholder="Ingresa tu correo" required class="login__input" id="input-email" name="correo">
                            </div>
    
                            <div>
                                <label for="input-pass" class="login__label">Password</label>
    
                                <div class="login__box">
                                    <input type="password" placeholder="Ingresa tu contrasena" required class="login__input" id="input-pass" name="contraseña">
                                    <i class="ri-eye-off-line login__eye" id="input-icon"></i>
                                </div>
                            </div>
                        </div>

                        <div class="login__check">
                            <input type="checkbox" class="login__check-input" id="input-check">
                            <label for="input-check" class="login__check-label">Remember me</label>
                        </div>
                    </div>

                    <div>
                        <div class="login__buttons">
                            <button class="login__button" type="submit" name="login">Log In</button>
                            <button class="login__button login__button-ghost" onclick="window.location.href='signup.php'">Sign Up</button>
                        </div>

                        <a href="#" class="login__forgot">Forgot Password?</a>
                    </div>
                </form>
            </div>
        </div>


        <!--=============== MAIN JS ===============-->
        <script src="assets/js/main.js"></script>
    </body>
</html>