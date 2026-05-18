<!DOCTYPE html>
<html>
<head>
    <title>Login MediFlow</title>
</head>
<body>

<h2>Login MediFlow</h2>

<form action="../controlador/loginControlador.php" method="POST">
    
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Contraseña:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Ingresar</button>

</form>

</body>
</html>