<?php
/**
 * tests/run_tests.php
 *
 * Ejecutor simple de pruebas funcionales (caja negra) contra la API.
 * Requisitos: PHP con cURL habilitado.
 *
 * USO:
 *   1) Ajusta BASE_URL si es necesario.
 *   2) Ejecuta: php tests/run_tests.php
 */

$BASE_URL = 'http://localhost/fans-cooperativa_2da_entrega_responsive_vFinal/frontend/api/api.php';

function http($method, $url, $jsonBody = null, $headers = [], $fileUpload = null) {
    $ch = curl_init();
    $opts = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HEADER         => false,
    ];
    if ($fileUpload) {
        // multipart/form-data
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = $fileUpload;
    } elseif ($jsonBody !== null) {
        $headers[] = 'Content-Type: application/json';
        $opts[CURLOPT_POSTFIELDS] = json_encode($jsonBody);
    }
    if ($headers) $opts[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $body, $err];
}

function out($msg, $ok = null) {
    if ($ok === null) { echo "— $msg\n"; return; }
    $emoji = $ok ? "✅" : "❌";
    echo "$emoji $msg\n";
}

function decode($body) {
    $data = json_decode($body, true);
    return $data === null ? ['_raw' => $body] : $data;
}

// ===== Comienza la suite =====
$fail = 0;

// 0) Ping básico (OPTIONS permitido)
list($code, $body, $err) = http('GET', $BASE_URL.'?action=list_receipts');
out("API responde (GET list_receipts) [$code]", $code >= 200 && $code < 500 && !$err);

// 1) Registrar usuario nuevo (queda PENDIENTE)
$email = 'test_api_'.date('Ymd_His').'_'.rand(100,999).'@example.com';
$payload = [
    'nombre'        => 'Test',
    'apellido'      => 'API',
    'correo'        => $email,
    'password'      => 'Password123',
    'fecha_ingreso' => date('Y-m-d'),
    'cedula'        => '4.123.456-7',
    'telefono'      => '091234567'
];
list($code, $body, $err) = http('POST', $BASE_URL.'?action=register', $payload);
$data = decode($body);
$ok = ($code === 201);
out("register -> HTTP $code", $ok); $fail += $ok ? 0 : 1;
if (!$ok) { out("Detalle: ".($data['message'] ?? $body)); }

// 2) Buscar en usuarios pendientes y obtener su id
list($code, $body, $err) = http('GET', $BASE_URL.'?action=pending_users');
$pend = decode($body);
$found = null;
if (isset($pend['users']) && is_array($pend['users'])) {
    foreach ($pend['users'] as $u) {
        if (isset($u['Correo']) && $u['Correo'] === $email) { $found = $u; break; }
    }
}
$ok = ($code === 200 && $found && isset($found['id_Residente']));
$uid = $ok ? $found['id_Residente'] : null;
out("pending_users contiene al registro ($email) -> id=$uid", $ok); $fail += $ok ? 0 : 1;

// 3) Aprobar al usuario
if ($uid) {
    list($code, $body, $err) = http('PUT', $BASE_URL.'?action=approve_user&id='.$uid);
    $ok = ($code === 200);
    out("approve_user id=$uid -> HTTP $code", $ok); $fail += $ok ? 0 : 1;
} else {
    out("approve_user saltado (no se obtuvo id)", false); $fail++;
}

// 4) Login con el usuario aprobado
list($code, $body, $err) = http('POST', $BASE_URL.'?action=login', ['correo' => $email, 'password' => 'Password123']);
$login = decode($body);
$ok = ($code === 200 && isset($login['user']['id']));
$residenteId = $ok ? $login['user']['id'] : null;
out("login -> HTTP $code, id_residente=$residenteId", $ok); $fail += $ok ? 0 : 1;

// 5) Registrar horas
if ($residenteId) {
    $payload = [
        'id_residente' => $residenteId,
        'fecha'        => date('Y-m-d'),
        'cantidad'     => 2.5,
        'descripcion'  => 'Prueba automática'
    ];
    list($code, $body, $err) = http('POST', $BASE_URL.'?action=add_hour', $payload);
    $ok = ($code === 201);
    out("add_hour -> HTTP $code", $ok); $fail += $ok ? 0 : 1;

    // 6) Listar horas del usuario
    list($code, $body, $err) = http('GET', $BASE_URL.'?action=list_hours&id_residente='.$residenteId);
    $hours = decode($body);
    $ok = ($code === 200 && isset($hours['hours']) && count($hours['hours']) > 0);
    out("list_hours(id_residente) -> HTTP $code, items=".($ok?count($hours['hours']):0), $ok); $fail += $ok ? 0 : 1;
} else {
    out("add_hour/list_hours saltado (no hay login)", false); $fail++;
}

// 7) Subir comprobante (multipart)
if ($residenteId) {
    $tmp = tempnam(sys_get_temp_dir(), 'rcp');
    // Escribimos un pseudo PDF mínimo
    file_put_contents($tmp, "%PDF-1.4\n% FANS test\n");

    $cfile = curl_file_create($tmp, 'application/pdf', 'comprobante_test.pdf');
    $multipart = [
        'id_residente' => (string)$residenteId,
        'tipo'         => 'Recibo de Prueba',
        'fecha'        => date('Y-m-d'),
        'monto'        => '123.45',
        'archivo'      => $cfile
    ];
    list($code, $body, $err) = http('POST', $BASE_URL.'?action=upload_receipt', null, [], $multipart);
    $up = decode($body);
    $ok = ($code === 201);
    out("upload_receipt -> HTTP $code", $ok); $fail += $ok ? 0 : 1;

    // 8) Listar comprobantes del usuario y aprobar el último
    list($code, $body, $err) = http('GET', $BASE_URL.'?action=list_receipts&id_residente='.$residenteId);
    $rc = decode($body);
    $okList = ($code === 200 && isset($rc['receipts']) && is_array($rc['receipts']) && count($rc['receipts'])>0);
    out("list_receipts(id_residente) -> HTTP $code, items=".($okList?count($rc['receipts']):0), $okList); $fail += $okList ? 0 : 1;

    if ($okList) {
        $last = $rc['receipts'][0];
        $rid = $last['id_Comprobante'] ?? null;
        if ($rid) {
            list($code, $body, $err) = http('PUT', $BASE_URL.'?action=approve_receipt&id='.$rid, ['id_admin' => 1]);
            $ok = ($code === 200);
            out("approve_receipt id=$rid -> HTTP $code", $ok); $fail += $ok ? 0 : 1;
        } else {
            out("approve_receipt saltado (no id_Comprobante)", false); $fail++;
        }
    }
} else {
    out("upload_receipt/list_receipts saltado (no hay login)", false); $fail++;
}

echo "\n==== Resultado: ".($fail ? "FALLÓ $fail prueba(s)" : "TODO OK")." ====\n";
exit($fail ? 1 : 0);
