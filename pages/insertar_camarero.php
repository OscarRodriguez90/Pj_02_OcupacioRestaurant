    <?php
    session_start();
    include '../database/conexion.php';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registrar Camarero</title>
        <link rel="stylesheet" href="./../styles/styles.css">
    </head>
    <body class="body-registro">
        <div class="container-registro">
            <form class="form-registro" action="../processes/insert_camarero_procesar.php" method="post">
                <div id="heading-registro">REGISTRO CAMARERO</div>
                
                <div class="form-group">
                    <label class="label-registro" for="nombre">Nombre</label>
                    <div class="field-registro">
                        <input class="input-field-registro" type="text" name="nombre" id="nombre" placeholder="Nombre">
                    </div>
                    <p id="nombreError"></p>
                </div>

                <div class="form-group">
                    <label class="label-registro" for="apellido">Apellido</label>
                    <div class="field-registro">
                        <input class="input-field-registro" type="text" name="apellido" id="apellido" placeholder="Apellido">
                    </div>
                    <p id="apellidoError"></p>
                </div>

                <div class="form-group">
                    <label class="label-registro" for="nombreUsuario">Usuario</label>
                    <div class="field-registro">
                        <input class="input-field-registro" type="text" name="username" id="username" placeholder="Usuario">
                    </div>
                    <p id="usernameError"></p>
                </div>

                <div class="form-group">
                    <label class="label-registro" for="dni">DNI</label>
                    <div class="field-registro">
                        <input class="input-field-registro" type="text" name="dni" id="dni" placeholder="DNI">
                    </div>
                    <p id="dniError"></p>
                </div>

                <div class="form-group">
                    <label class="label-registro" for="telefono">Teléfono</label>
                    <div class="field-registro">
                        <input class="input-field-registro" type="text" name="telefono" id="telefono" placeholder="Teléfono"ç>
                    </div>
                    <p id="telefonoError"></p>
                </div>

                <div class="form-group">
                    <label class="label-registro" for="correo">Correo</label>
                    <div class="field-registro">
                        <input class="input-field-registro" type="email" name="correo" id="correo" placeholder="Correo">
                    </div>
                    <p id="correoError"></p>
                </div>

                <div class="form-group">
                    <label class="label-registro" for="fecha">Fecha</label>
                    <div class="field-registro">
                        <input class="input-field-registro" type="date" name="fecha" id="fecha">
                    </div>
                    <p id="fechaError"></p>
                </div>

                <div class="form-group">
                    <label class="label-registro" for="password">Contraseña</label>
                    <div class="field-registro">
                        <input class="input-field-registro" type="password" name="password" id="password" placeholder="Contraseña">
                    </div>
                    <p id="passwordError"></p>
                </div>
                <!-- confirmar contraseña -->
                <div class="form-group">
                    
                    <label class="label-registro" for="confirm_password">Confirmar Contraseña</label>
                    <div class="field-registro">
                    <input class="input-field-registro" type="password" name="confirm_password" id="confirm_password" placeholder="Confirmar Contraseña">
                    </div>
                    <p id="confirmPasswordError"></p>
                </div>
                <div class="btn-registro">
                    <input type="submit" value="Registrar" name="Registrar" class="button-registro">
                    <a href="./login.php" class="button-registro button-volver">Volver</a>
                </div>
                <?php
                if (isset($_SESSION['error']) && is_array($_SESSION['error'])) {
                    foreach ($_SESSION['error'] as $err) {
                        echo "<p class='error'>$err</p>";
                    }
                    unset($_SESSION['error']);
                } else {
                    echo "<p class='error-message' id='error'></p>";
                }
                ?>
            </form>
        </div>
    </body>
    <script src="../js/script.js"></script>
    </html>