<?php
// C:\xampp\htdocs\fans-cooperativa\frontend\api\api.php

// Habilitar la visualización de errores (SOLO EN DESARROLLO)
// Asegúrate de DESHABILITAR esto en un entorno de producción por seguridad
// Método HTTP (en mayúsculas). Soporta override simple por formulario si hiciera falta.
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? ($_POST['_method'] ?? 'GET'));

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Encabezados CORS (permitir que tu frontend acceda a esta API)
header("Access-Control-Allow-Origin: *"); // Permite desde cualquier origen (para desarrollo)
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
 // Indicamos que la respuesta será JSON
set_error_handler(function($errno,$errstr,$errfile,$errline){
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['message'=>'PHP error','detail'=>"$errstr in $errfile:$errline"]);
  exit();
});
set_exception_handler(function($ex){
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['message'=>'Exception','detail'=>$ex->getMessage()]);
  exit();
});

// Manejar solicitudes preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ==== Helpers de validación y utilidades ====
function json_error($code, $msg) {
    http_response_code($code);
    echo json_encode(['message' => $msg]);
    exit();
}
function is_valid_date($d) {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d;
}
function is_past_or_today($d) {
    try { $dt = new DateTime($d); $today = new DateTime('today'); return $dt <= $today; } catch (Exception $e) { return false; }
}
function is_positive_number($n) {
    return is_numeric($n) && $n > 0;
}
function strong_password($p) {
    // >=8 chars, al menos 1 letra y 1 número
    return is_string($p) && strlen($p) >= 8 && preg_match('/[A-Za-z]/', $p) && preg_match('/\d/', $p);
}
function valid_email($e) { return filter_var($e, FILTER_VALIDATE_EMAIL); }
function sanitize_text($s) { return trim(filter_var($s ?? '', FILTER_SANITIZE_STRING)); }



// --- Configuración de la Base de Datos (¡EDITA ESTO SI ES NECESARIO!) ---
$dbHost = 'localhost';
$dbName = 'fans_cooperativa'; // Nombre de tu base de datos
$dbUser = 'root';
$dbPass = ''; // TU CONTRASEÑA DE LA BASE DE DATOS (déjala vacía si no tienes)

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit();
}
// --- Fin de la Configuración de la Base de Datos ---


// Obtener los datos de la solicitud (frontend envía JSON)
$data = json_decode(file_get_contents("php://input"), true);

// Obtener la acción desde el parámetro GET (ej: ?action=register o ?action=login)
$action = $_GET['action'] ?? '';
// Al inicio, antes del switch/router
$raw = file_get_contents('php://input');
if (!empty($raw) && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $_POST = array_merge($_POST, $json);
    }
}

switch ($action) {
    case 'register':
        $nombre = $data['nombre'] ?? '';
        $apellido = $data['apellido'] ?? '';
        $correo = $data['correo'] ?? '';
        $password = $data['password'] ?? '';
        $fecha_ingreso = $data['fecha_ingreso'] ?? date('Y-m-d');
        $cedula = $data['cedula'] ?? null;
        $telefono = $data['telefono'] ?? null;

        // Validación básica de datos
        if (empty($nombre) || empty($apellido) || empty($correo) || empty($password)) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Todos los campos obligatorios deben ser completados.']);
            exit();
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['message' => 'El formato del correo electrónico es inválido.']);
            exit();
        }
        if (strlen($password) < 8) {
            http_response_code(400);
            echo json_encode(['message' => 'La contraseña debe tener al menos 8 caracteres.']);
            exit();
        }

        // Validaciones adicionales
        if (!valid_email($correo)) { json_error(400, 'Correo inválido.'); }
        if (!strong_password($password)) { json_error(400, 'Contraseña débil: mínimo 8 caracteres, con letras y números.'); }
        if ($telefono && !preg_match('/^[0-9 +()-]{6,}$/', $telefono)) { json_error(400, 'Teléfono inválido.'); }
        if ($cedula && !preg_match('/^[0-9\.\-A-Za-z]{5,}$/', $cedula)) { json_error(400, 'Cédula inválida.'); }
        if ($fecha_ingreso && !is_valid_date($fecha_ingreso)) { json_error(400, 'Fecha de ingreso inválida (YYYY-MM-DD).'); }

        // Hashear la contraseña
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            // Verificar si el correo ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Residente WHERE Correo = ?");
            $stmt->execute([$correo]);
            if ($stmt->fetchColumn() > 0) {
                http_response_code(409); // Conflict
                echo json_encode(['message' => 'El correo electrónico ya está registrado.']);
                exit();
            }

            // Insertar nuevo usuario. estado_aprobacion por defecto es FALSE (0)
            $stmt = $pdo->prepare("INSERT INTO Residente (Nombre, Apellido, Cedula, Correo, Telefono, Fecha_Ingreso, Contrasena, estado_aprobacion) VALUES (?, ?, ?, ?, ?, ?, ?, FALSE)");
            $stmt->execute([$nombre, $apellido, $cedula, $correo, $telefono, $fecha_ingreso, $hashed_password]);

            http_response_code(201); // Created
            echo json_encode(['message' => 'Registro exitoso. Su cuenta está pendiente de aprobación por un administrador.']);
        } catch (PDOException $e) {
            http_response_code(500); // Internal Server Error
            echo json_encode(['message' => 'Error interno del servidor al registrar: ' . $e->getMessage()]);
        }
        break;

    case 'login':
    // Lee JSON
    $correo   = $data['correo']   ?? '';
    $password = $data['password'] ?? '';

    if ($correo === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['message' => 'Correo y contraseña son requeridos.']);
        break;
    }

    try {
        // (Opcional) fuerza collation si venías con problemas de 1267
        // $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");

        // =========================
        // 1) Intento como ADMIN
        // =========================
        $sqlA = "
            SELECT
                id_Administrador AS id,
                Nombre,
                Apellido,
                Correo,
                Contrasena,
                estado_aprobacion,
                rol
            FROM Administrador
            WHERE (Correo = :login OR Usuario = :login)
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sqlA);
        $stmt->execute([':login' => $correo]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['Contrasena'])) {
            if ((int)$admin['estado_aprobacion'] === 1) {
                unset($admin['Contrasena']);
                // Garantiza campo role
                $admin['role'] = $admin['rol'] ?: 'admin';

                http_response_code(200);
                echo json_encode([
                    'message' => 'Inicio de sesión exitoso.',
                    'user'    => $admin
                ]);
                break;
            } else {
                http_response_code(403);
                echo json_encode(['message' => 'Su cuenta de administrador está pendiente de aprobación.']);
                break;
            }
        }

        // =========================
        // 2) Intento como RESIDENTE
        // (con campos extra + alias de fecha)
        // =========================
        $sqlR = "
            SELECT
                id_Residente   AS id,
                Nombre,
                Apellido,
                Correo,
                Contrasena,
                estado_aprobacion,
                rol,
                Cedula,
                Telefono,
                Fecha_Ingreso  AS FechaIngreso
            FROM Residente
            WHERE Correo = :correo
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sqlR);
        $stmt->execute([':correo' => $correo]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['Contrasena'])) {
            if ((int)$user['estado_aprobacion'] === 1) {
                unset($user['Contrasena']);
                $user['role'] = $user['rol'] ?: 'residente';

                http_response_code(200);
                echo json_encode([
                    'message' => 'Inicio de sesión exitoso.',
                    'user'    => $user
                ]);
            } else {
                http_response_code(403);
                echo json_encode(['message' => 'Su cuenta está pendiente de aprobación por un administrador.']);
            }
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Credenciales inválidas.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error interno del servidor al iniciar sesión: ' . $e->getMessage()]);
    }
    break;




    // --- Acciones para el Backoffice ---

   case 'pending_users':
    if ($method !== 'GET') { http_response_code(405); echo json_encode(['message' => 'Método no permitido. Use GET.']); break; }
    try {
        $stmt = $pdo->query("
            SELECT 
                id_Residente,
                Nombre,
                Apellido,
                Correo,
                Telefono,
                Fecha_Ingreso AS fecha_registro
            FROM Residente
            WHERE estado_aprobacion = 0
            ORDER BY id_Residente DESC
        ");
        $users = $stmt->fetchAll();
        echo json_encode(['users' => $users]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al obtener usuarios pendientes: ' . $e->getMessage()]);
    }
    break;

    case 'approve_user': // Aprobar un usuario por ID
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            // El ID del usuario viene en el parámetro GET 'id' (ej. ?action=approve_user&id=123)
            $userId = $_GET['id'] ?? null; 

            if (!$userId || !is_numeric($userId)) {
                http_response_code(400); // Bad Request
                echo json_encode(['message' => 'ID de usuario para aprobar no proporcionado o inválido.']);
                exit();
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE Residente SET estado_aprobacion = TRUE WHERE id_Residente = ?");
                $stmt->execute([$userId]);

                if ($stmt->rowCount() > 0) {
                    http_response_code(200);
                    echo json_encode(['message' => 'Usuario aprobado exitosamente.']);
                } else {
                    http_response_code(404); // Not Found si el ID no existe o ya estaba aprobado
                    echo json_encode(['message' => 'Usuario no encontrado o ya aprobado.']);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['message' => 'Error al aprobar usuario: ' . $e->getMessage()]);
            }
        } else {
            http_response_code(405); // Método no permitido
            echo json_encode(['message' => 'Método no permitido. Use PUT para aprobar usuarios.']);
        }
        break;

    
case 'add_hour':
    // Body JSON: { "id_residente": 1, "fecha": "2025-08-01", "cantidad": 5.0, "descripcion": "Jornada de limpieza" }
    if ($method !== 'POST') { http_response_code(405); echo json_encode(['message' => 'Método no permitido. Use POST.']); break; }
    $id_residente = $data['id_residente'] ?? null;
    $fecha = $data['fecha'] ?? null;
    $cantidad = $data['cantidad'] ?? null;
    $descripcion = $data['descripcion'] ?? null;
    if (!$id_residente || !$fecha || !$cantidad) {
        http_response_code(400); echo json_encode(['message' => 'id_residente, fecha y cantidad son requeridos.']); break;
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO Horas_Trabajo (id_Residente, Fecha, Cantidad, Descripcion) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_residente, $fecha, $cantidad, $descripcion]);
        http_response_code(201);
        echo json_encode(['message' => 'Horas registradas correctamente.']);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['message' => 'Error al registrar horas: ' . $e->getMessage()]);
    }
    break;



case 'list_hours':
    // GET ?action=list_hours&id_residente=1
    if ($method !== 'GET') { http_response_code(405); echo json_encode(['message' => 'Método no permitido. Use GET.']); break; }
    $id_residente = $_GET['id_residente'] ?? null;
    if (!$id_residente) { http_response_code(400); echo json_encode(['message' => 'id_residente es requerido.']); break; }
    try {
        $stmt = $pdo->prepare("SELECT id_Hora, Fecha, Cantidad, Descripcion, Fecha_Registro FROM Horas_Trabajo WHERE id_Residente = ? ORDER BY Fecha DESC, id_Hora DESC");
        $stmt->execute([$id_residente]);
        $rows = $stmt->fetchAll();
        echo json_encode(['hours' => $rows]);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['message' => 'Error al listar horas: ' . $e->getMessage()]);
    }
    break;



case 'upload_receipt':
    // POST multipart/form-data: id_residente, tipo, fecha, monto (opcional), archivo (file)
    if ($method === 'OPTIONS') { http_response_code(204); exit(); }
    if ($method !== 'POST') { http_response_code(405); echo json_encode(['message' => 'Método no permitido. Use POST.']); break; }

    // Asegurar carpeta de uploads
    $uploadDir = __DIR__ . '/../uploads/comprobantes';
    if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

    $id_residente = $_POST['id_residente'] ?? null;
    $tipo = $_POST['tipo'] ?? null;
    $fecha = $_POST['fecha'] ?? null;
    $monto = $_POST['monto'] ?? null;

    if (!$id_residente || !$tipo || !$fecha || !isset($_FILES['archivo'])) {
        http_response_code(400); echo json_encode(['message' => 'id_residente, tipo, fecha y archivo son requeridos.']); break;
    }

    $file = $_FILES['archivo'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400); echo json_encode(['message' => 'Error en la subida de archivo.']); break;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['pdf','jpg','jpeg','png'];
    if (!in_array(strtolower($ext), $allowed)) {
        http_response_code(400); echo json_encode(['message' => 'Formato no permitido. Usa PDF/JPG/PNG.']); break;
    }

    $safeName = 'comp_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/','_', $file['name']);
    $dest = $uploadDir . '/' . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        http_response_code(500); echo json_encode(['message' => 'No se pudo guardar el archivo.']); break;
    }

    $relative = 'uploads/comprobantes/' . $safeName;

    try {
        // Guardar en la tabla Comprobantes_Pago si existe, sino en Comprobante
        $table = 'Comprobantes_Pago';
        try {
            $pdo->query("SELECT 1 FROM Comprobantes_Pago LIMIT 1");
        } catch (Exception $e) { $table = 'Comprobante'; }

        $stmt = $pdo->prepare("INSERT INTO $table (Tipo, Fecha, Monto, Estado, Archivo, id_Residente) VALUES (?, ?, ?, 'Pendiente', ?, ?)");
        $stmt->execute([$tipo, $fecha, $monto, $relative, $id_residente]);

        http_response_code(201);
        echo json_encode(['message' => 'Comprobante subido y registrado.', 'archivo' => $relative]);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['message' => 'Error al guardar comprobante: ' . $e->getMessage()]);
    }
    break;



case 'list_receipts':
    // GET ?action=list_receipts&estado=Pendiente (opcional) &id_residente=1 (opcional)
    if ($method !== 'GET') { http_response_code(405); echo json_encode(['message' => 'Método no permitido. Use GET.']); break; }
    $estado = $_GET['estado'] ?? null;
    $id_residente = $_GET['id_residente'] ?? null;
    $table = 'Comprobantes_Pago';
    try { $pdo->query("SELECT 1 FROM Comprobantes_Pago LIMIT 1"); } catch (Exception $e) { $table = 'Comprobante'; }

    $sql = "SELECT id_Comprobante, Tipo, Fecha, Monto, Estado, Archivo, Fecha_Subida, id_Residente FROM $table WHERE 1=1";
    $params = [];
    if ($estado) { $sql .= " AND Estado = ?"; $params[] = $estado; }
    if ($id_residente) { $sql .= " AND id_Residente = ?"; $params[] = $id_residente; }
    $sql .= " ORDER BY Fecha_Subida DESC, id_Comprobante DESC";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['receipts' => $stmt->fetchAll()]);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['message' => 'Error al listar comprobantes: ' . $e->getMessage()]);
    }
    break;



case 'approve_receipt':
    // PUT ?action=approve_receipt&id=###  Body JSON opcional: { "id_admin": 1 }
    if ($method !== 'PUT') { http_response_code(405); echo json_encode(['message' => 'Método no permitido. Use PUT.']); break; }
    $id = $_GET['id'] ?? null;
    $body = file_get_contents("php://input");
    $dataBody = json_decode($body, true);
    $id_admin = $dataBody['id_admin'] ?? null;

    if (!$id) { http_response_code(400); echo json_encode(['message' => 'Parámetro id requerido.']); break; }
    $table = 'Comprobantes_Pago';
    try { $pdo->query("SELECT 1 FROM Comprobantes_Pago LIMIT 1"); } catch (Exception $e) { $table = 'Comprobante'; }

    try {
        if ($id_admin) {
            $stmt = $pdo->prepare("UPDATE $table SET Estado='Aprobado', id_Administrador=? WHERE id_Comprobante=?");
            $stmt->execute([$id_admin, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE $table SET Estado='Aprobado' WHERE id_Comprobante=?");
            $stmt->execute([$id]);
        }
        echo json_encode(['message' => 'Comprobante aprobado.']);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['message' => 'Error al aprobar comprobante: ' . $e->getMessage()]);
    }
    break;


default:
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Acción no especificada o no válida.']);
        break;
} ?>