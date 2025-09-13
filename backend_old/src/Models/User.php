<?php
// backend/src/Models/User.php

require_once __DIR__ . '/../Database.php';

class User {
    private $conn;
    private $table_name = "Residente"; // O Administrador si fuera el caso

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    public function register($nombre, $apellido, $cedula, $correo, $telefono, $fecha_ingreso, $password) {
        // Validación básica
        if (empty($nombre) || empty($apellido) || empty($correo) || empty($password)) {
            return false; // Datos incompletos
        }

        // Hashear la contraseña
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insertar en la base de datos
        $query = "INSERT INTO " . $this->table_name . " (Nombre, Apellido, Cedula, Correo, Telefono, Fecha_Ingreso, Contrasena, estado_aprobacion) VALUES (?, ?, ?, ?, ?, ?, ?, FALSE)";
        $stmt = $this->conn->prepare($query);

        // Asumiendo que id_Vivienda se asignará después o puede ser NULL inicialmente
        // Ajusta los tipos de datos si es necesario (ej. fecha_ingreso como 'YYYY-MM-DD')
        return $stmt->execute([$nombre, $apellido, $cedula, $correo, $telefono, $fecha_ingreso, $hashed_password]);
    }

    public function login($correo, $password) {
        $query = "SELECT id_Residente, Nombre, Apellido, Correo, Contrasena, estado_aprobacion FROM " . $this->table_name . " WHERE Correo = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$correo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Contrasena'])) {
            // Verificar si el usuario está aprobado
            if ($user['estado_aprobacion']) {
                // Autenticación exitosa, no retornamos la contraseña
                unset($user['Contrasena']);
                return $user;
            } else {
                return ['status' => 'pending_approval']; // Usuario no aprobado
            }
        }
        return false; // Credenciales inválidas
    }

    public function getAllPendingApproval() {
        $query = "SELECT id_Residente, Nombre, Apellido, Correo, Fecha_Registro FROM " . $this->table_name . " WHERE estado_aprobacion = FALSE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approveUser($id_residente) {
        $query = "UPDATE " . $this->table_name . " SET estado_aprobacion = TRUE WHERE id_Residente = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_residente]);
    }
}