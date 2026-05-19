<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediFlow - Iniciar Sesión</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f9;
            color: #333;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 35px;
            border-top: 5px solid #0b5687;
        }

        .brand {
            text-align: center;
            margin-bottom: 25px;
        }

        .brand h2 {
            margin: 0;
            color: #0b5687;
            font-size: 28px;
            font-weight: 600;
        }

        .brand h2 span {
            color: #4fc3f7;
        }

        .brand p {
            margin: 5px 0 0 0;
            color: #777;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
            color: #555;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #0b5687;
            outline: none;
            box-shadow: 0 0 5px rgba(11, 86, 135, 0.2);
        }

        .btn-submit {
            background-color: #0b5687;
            color: white;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background-color: #083f63;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="card">
        <div class="brand">
            <h2>MediFlow<span>•</span></h2>
            <p>Gestión del Sistema de Obra Social</p>
        </div>

        <form action="../controlador/loginControlador.php" method="POST">
            
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" placeholder="ejemplo@test.com" required>
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Ingresar al Sistema</button>

        </form>
    </div>
    <div class="footer-text">
        &copy; 2026 MediFlow Inc. Todos los derechos reservados.
    </div>
</div>

</body>
</html>