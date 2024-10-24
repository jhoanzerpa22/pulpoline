<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulpo Line - Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        async function register(event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            const data = {
                username: username,
                email: email,
                password: password
            };

            try {
                const response = await fetch('https://pulpoline.onrender.com/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                alert('Usuario registrado exitosamente');
            } catch (error) {
                console.error('Error al registrar el usuario:', error);
                alert('Error al registrar el usuario');
            }
        }
    </script>
</head>
<body class="bg-gray-800 flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center">Pulpo Line - Registro</h2>
        <form onsubmit="register(event)">
            <div class="mb-4">
                <label for="username" class="block text-gray-700">Nombre de Usuario</label>
                <input type="text" id="username" name="username" required class="mt-1 block w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Correo Electrónico</label>
                <input type="email" id="email" name="email" required class="mt-1 block w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700">Contraseña</label>
                <input type="password" id="password" name="password" required class="mt-1 block w-full p-2 border border-gray-300 rounded">
            </div>
            <button type="submit" class="w-full bg-green-500 text-white p-2 rounded hover:bg-green-600">Registrar</button>
        </form>
        <p class="mt-4 text-center">
            ¿Ya tienes una cuenta? <a href="index.php" class="text-blue-500 hover:underline">Inicia sesión aquí</a>
        </p>
    </div>
</body>
</html>