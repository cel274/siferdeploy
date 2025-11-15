<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registro - SIFER</title>
  <link rel="stylesheet" href="./css/style.css" />
  <link rel="stylesheet" href="./css/login.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <style>
    .error {
      display: none;
      color: red;
      margin-top: 5px;
      font-size: 0.9em;
    }
  </style>
</head>
<body class="login-page">
  <div class="login-container">
    <div class="login-card">
      <h2>Crear Cuenta</h2>
      
      <form id="registroForm" action="autentificar_registro.php" method="POST">
        <input type="text" id ="usuario"name="usuario" placeholder="Usuario" required>
        <input type="password" id="contraseña" name="contraseña" placeholder="Contraseña" required>
        <input type="password" id="confirmar" placeholder="Confirmar contraseña" required>
        <div id="errorMensaje" class="error" style="padding-bottom:10px;">Las contraseñas no coinciden</div>
        <div id ="errorMensaje2" class ="error" style="padding-bottom:10px;"> Nombre de usuario o contraseñas muy cortos</div>
        
        <button type="submit">Registrarse</button>

        <p class="register-link">¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a></p>
        <p class="register-link">O entrá como <a href="index.php">Invitado</a></p>
      </form>

      <script>
        const nombre = document.getElementById('usuario')
        const form = document.getElementById('registroForm');
        const pass = document.getElementById('contraseña');
        const confirm = document.getElementById('confirmar');
        const error = document.getElementById('errorMensaje');
        const error2 = document.getElementById('errorMensaje2')

        form.addEventListener('submit', (e) => {
          if (nombre.value.trim().length < 3){
            e.preventDefault()
            error2.style.display = 'block';
          } else {
            error2.style.display='none';
          }
          if (pass.value !== confirm.value) {
            e.preventDefault();
            error.style.display = 'block';
          } else {
            error.style.display = 'none';
          }
        });
      </script>

    </div>
  </div>
</body>
</html>
