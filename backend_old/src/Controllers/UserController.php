<?php
// backend/src/Controllers/UserController.php

require_once __DIR__ . '/../Models/User.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $nombre = $data['nombre'] ?? '';
        $apellido = $data['apellido'] ?? '';
        $cedula = $data['cedula'] ?? null; // Puede ser opcional
        $correo = $data['correo'] ?? '';
        $telefono = $data['telefono'] ?? null; // Puede ser opcional
        $fecha_ingreso = $data['fecha_ingreso'] ?? date('Y-m-d'); // Default a hoy
        $password = $data['password'] ?? '';

        if (empty($nombre) || empty($apellido) || empty($correo) || empty($password)) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Todos los campos obligatorios deben ser completados.']);
            return;
        }

        if ($this->userModel->register($nombre, $apellido, $cedula, $correo, $telefono, $fecha_ingreso, $password)) {
            http_response_code(201); // Created
            echo json_encode(['message' => 'Registro exitoso. Su cuenta está pendiente de aprobación.']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['message' => 'Error al registrar usuario. El correo podría ya estar en uso.']);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $correo = $data['correo'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($correo) || empty($password)) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Correo y contraseña son requeridos.']);
            return;
        }

        $user = $this->userModel->login($correo, $password);

        if ($user === false) {
            http_response_code(401); // Unauthorized
            echo json_encode(['message' => 'Credenciales inválidas.']);
        } elseif (is_array($user) && isset($user['status']) && $user['status'] === 'pending_approval') {
            http_response_code(403); // Forbidden
            echo json_encode(['message' => 'Su cuenta está pendiente de aprobación por un administrador.']);
        } else {
            http_response_code(200); // OK
            // Iniciar sesión, generar token JWT (para futuras entregas)
            echo json_encode(['message' => 'Inicio de sesión exitoso.', 'user' => $user]);
        }
    }

    // Métodos para el Backoffice
    public function getPendingUsers() {
        $users = $this->userModel->getAllPendingApproval();
        http_response_code(200);
        echo json_encode(['users' => $users]);
    }

    public function approveUser($id) {
        if ($this->userModel->approveUser($id)) {
            http_response_code(200);
            echo json_encode(['message' => 'Usuario aprobado exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Error al aprobar usuario.']);
        }
    }
}