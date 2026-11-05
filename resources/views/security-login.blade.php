<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login de seguridad</title>
</head>
<body>
    <h2>Prueba de autenticación</h2>
    <form method="POST" action="/security-login">
        @csrf
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
