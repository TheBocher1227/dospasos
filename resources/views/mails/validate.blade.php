<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email de Activación</title>
    <style>
        /* Estilos generales para el correo */
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            color: #444;
            margin-bottom: 20px;
            font-weight: 600;
        }

        p {
            font-size: 16px;
            line-height: 1.5;
            color: #666;
            margin: 10px 0;
        }

        .cta-button {
            display: inline-block;
            padding: 12px 25px;
            margin: 20px 0;
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            border-radius: 50px;
            transition: background-color 0.3s;
        }

        .cta-button:hover {
            background-color: #0056b3;
        }

        .footer {
            font-size: 14px;
            color: #aaa;
            text-align: center;
            margin-top: 30px;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }

        /* Estilos de la cabecera y la estructura */
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 20px;
        }

        .logo {
            max-width: 120px;
            margin-bottom: 10px;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- Puedes agregar tu logotipo aquí -->
            <img src="https://via.placeholder.com/120" alt="Logo" class="logo">
            <h1>¡Bienvenido a TecnoTruck!</h1>
        </div>

        <p>Hola,</p>
        <p>Gracias por registrarte en TecnoTruck. Para activar tu cuenta, por favor haz clic en el siguiente enlace:</p>

        <!-- Botón de activación -->
        <a href="{{ $signedroute }}" class="cta-button">Activar mi cuenta</a>

        <p>Una vez que actives tu cuenta, podrás acceder a todos los beneficios de nuestra plataforma.</p>
        <p>¡Esperamos que disfrutes de la experiencia!</p>

        <div class="footer">
            <p>Si no has solicitado esta cuenta, ignora este mensaje.</p>
            <p>© 2025 TecnoTruck. Todos los derechos reservados.</p>
            <p><a href="https://www.tecnotruck.com">Visita nuestro sitio web</a></p>
        </div>
    </div>
</body>
</html>
