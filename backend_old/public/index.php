<?php
// backend/public/index.php

// Habilitar la visualización de errores para depuración (SOLO EN DESARROLLO)
// Asegúrate de DESHABILITAR esto en un entorno de producción por seguridad
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Encabezados CORS (Permitir peticiones desde tu frontend)
header("Access-Control-Allow-Origin: *"); // Permite CORS desde cualquier origen (para desarrollo)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Manejar solicitudes preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir los controladores y modelos necesarios
require_once __DIR__ . '/../src/Controllers/UserController.php';
require_once __DIR__ . '/../src/Database.php'; // Asegúrate de que Database.php esté en src/


$requestMethod = $_SERVER['REQUEST_METHOD'];

// --- Lógica de Ruteo SIMPLE CON parámetro GET 'route' (¡ESTA ES LA CLAVE!) ---
// Si la URL que viene del frontend es http://.../index.php?route=users/register
// esta lógica lee el valor del parámetro 'route'.

$api_path_relative = isset($_GET['route']) ? trim($_GET['route'], '/') : '';
$api_segments = explode('/', $api_path_relative);
$api_segments = array_filter($api_segments, function($value) { return $value !== ''; }); // Elimina segmentos vacíos
$api_segments = array_values($api_segments); // Reindexa el array
// --- FIN de la Lógica de Ruteo SIMPLE ---


// Si no hay segmentos de API (ej. solo /index.php o /index.php?route=)
if (empty($api_segments[0])) {
    http_response_code(200); // OK
    echo json_encode(['message' => 'Bienvenido a la API de F.A.N.S (modo de ruteo simplificado).']);
    exit();
}

// Crear una instancia del controlador de usuarios
$controller = new UserController();

// Ruteador simple (el resto de tu lógica de switch es la misma)
if (isset($api_segments[0])) {
    switch ($api_segments[0]) {
        case 'users':
            if (isset($api_segments[1])) {
                switch ($api_segments[1]) {
                    case 'register':
                        if ($requestMethod === 'POST') {
                            $controller->register();
                        } else {
                            http_response_code(405); // Method Not Allowed
                            echo json_encode(['message' => 'Método no permitido para registro. Use POST.']);
                        }
                        break;
                    case 'login':
                        if ($requestMethod === 'POST') {
                            $controller->login();
                        } else {
                            http_response_code(405); // Method Not Allowed
                            echo json_encode(['message' => 'Método no permitido para login. Use POST.']);
                        }
                        break;
                    case 'pending': // Para el backoffice
                        if ($requestMethod === 'GET') {
                            $controller->getPendingUsers();
                        } else {
                            http_response_code(405);
                            echo json_encode(['message' => 'Método no permitido. Use GET.']);
                        }
                        break;
                    case 'approve': // Para el backoffice
                        if ($requestMethod === 'PUT' && isset($api_segments[2])) {
                            $userId = (int)$api_segments[2]; // El ID a aprobar está en $api_segments[2]
                            $controller->approveUser($userId);
                        } else {
                            http_response_code(400);
                            echo json_encode(['message' => 'ID de usuario para aprobar no proporcionado o método no permitido. Use PUT.']);
                        }
                        break;
                    default:
                        http_response_code(404);
                        echo json_encode(['message' => 'Endpoint de usuario no encontrado.']);
                        break;
                }
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Endpoint de usuario no especificado.']);
            }
            break;
        // Aquí se agregarían otros 'case' para más recursos de API (ej. 'viviendas', 'pagos')
        default:
            http_response_code(404);
            echo json_encode(['message' => 'Recurso de API no encontrado.']);
            break;
    }
} else {
    http_response_code(404);
    echo json_encode(['message' => 'Ruta de API no válida.']);
}

?>