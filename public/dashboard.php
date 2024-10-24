<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulpo Line</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function toggleForm() {
            const form = document.getElementById('formulario');
            form.classList.toggle('hidden');
        }

        async function logout() {
            try {
                const response = await fetch('http://localhost:3000/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    }
                });

                if (response) {
                    localStorage.removeItem('token');
                    window.location.href = 'index.php';
                }
            } catch (error) {
                console.error('Error al cerrar sesión:', error);
                alert('Error al cerrar sesión');
            }
        }

        async function initial() {
            
            const token = localStorage.getItem('token');

            try {
                const response = await fetch('http://localhost:3000/list-tokens', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    }
                });

                const result = await response.json();
                console.log(result);
            } catch (error) {
                console.error('Error al obtener listado de tokens:', error);
            }
        }

        async function createToken(event) {
            event.preventDefault();

            const name = document.getElementById('nombre').value;
            const symbol = document.getElementById('simbolo').value;
            const initialSupply = document.getElementById('initialSupply').value;

            const data = {
                name: name,
                symbol: symbol,
                initialSupply: initialSupply
            };

            const token = localStorage.getItem('token');

            try {
                const response = await fetch('http://localhost:3000/create-token-hedera', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                alert('Token registrado exitosamente');
            } catch (error) {
                console.error('Error al registrar el token:', error);
                alert('Error al registrar el token');
            }
        }

        function verificarToken() {
            const token = localStorage.getItem('token');

            if (!token) {
                window.location.href = 'index.php';
            }
        }

        //initial();
        
        document.addEventListener('DOMContentLoaded', verificarToken);
    </script>
</head>
<body class="bg-gray-800">
    <div class="container mx-auto p-6 bg-gray-700">
        
        <div class="relative w-full">
            <div class="absolute top-0 right-0">
                <button onclick="logout()" class="mb-4 bg-red-500 text-white p-2 rounded hover:bg-red-600 right">Cerrar sesión</button>
            </div>
        </div>

        <h2 class="text-2xl font-bold mb-6 text-center text-white">Dashboard</h2>
        
        <button onclick="toggleForm()" class="mb-4 bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Agregar Nuevo Token</button>

        <div id="formulario" class="hidden mb-6 bg-white p-6 rounded shadow-md">
            <h3 class="text-xl text-center font-bold mb-4">Agregar Nuevo Token</h3>
            <form onsubmit="createToken(event)">
                <div class="grid grid-cols-3 gap-3">
                    <div class="mb-4 p-4">
                        <label for="nombre" class="block text-gray-700">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required class="mt-1 block w-full p-2 border border-gray-300 rounded">
                    </div>
                    <div class="mb-4 p-4">
                        <label for="simbolo" class="block text-gray-700">Símbolo</label>
                        <input type="text" id="simbolo" name="simbolo" required class="mt-1 block w-full p-2 border border-gray-300 rounded">
                    </div>
                    <div class="mb-4 p-4">
                        <label for="initialSupply" class="block text-gray-700">Suministro Inicial</label>
                        <input type="number" id="initialSupply" name="initialSupply" required class="mt-1 block w-full p-2 border border-gray-300 rounded">
                    </div>
                </div>
                <button type="submit" class="w-full bg-green-500 text-white p-2 rounded hover:bg-green-600">Agregar</button>
            </form>
        </div>

        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">Nombre</th>
                    <th class="py-2 px-4 border-b text-center">Símbolo</th>
                    <th class="py-2 px-4 border-b text-right">Suministro Inicial</th>
                </tr>
            </thead>
            <tbody>
                <!--<tr>
                    <td class="py-2 px-4 border-b">Token 1</td>
                    <td class="py-2 px-4 border-b text-center">T1</td>
                    <td class="py-2 px-4 border-b text-right">100</td>
                </tr>-->
            </tbody>
        </table>
    </div>
</body>
</html>