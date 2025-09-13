<?php
/**
 * tests/index.php
 *
 * Ejecuta automáticamente las pruebas de API y Backoffice
 * y muestra el resultado en el navegador como una página web.
 */

// URL base de tu API (ajustala si tu carpeta cambia)
$BASE_URL = 'http://localhost/fans-cooperativa_2da_entrega_responsive_v3/frontend/api/api.php';

function http($method, $url, $jsonBody = null, $fileUpload = null) {
    $ch = curl_init();
    $opts = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HEADER         => false,
    ];
    if ($fileUpload) {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = $fileUpload;
    } elseif ($jsonBody !== null) {
        $opts[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
        $opts[CURLOPT_POSTFIELDS] = json_encode($jsonBody);
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $body];
}

function show($msg, $ok) {
    $color = $ok ? "green" : "red";
    echo "<p style='color:$color'>".($ok ? "✅" : "❌")." $msg</p>";
}

// ===== Comienzan las pruebas =====
echo "<h2>Resultados de Tests - Cooperativa</h2>";

list($code, $body) = http('GET', $BASE_URL.'?action=list_receipts');
show("API responde (list_receipts) HTTP $code", $code >= 200 && $code < 500);

// Registrar usuario de prueba
$email = "test_web_".time()."@example.com";
$payload = [
    'nombre' => 'Test',
    'apellido' => 'Web',
    'correo' => $email,
    'password' => 'Password123',
    'fecha_ingreso' => date('Y-m-d'),
    'cedula' => '5.123.456-7',
    'telefono' => '099123456'
];
list($code, $body) = http('POST', $BASE_URL.'?action=register', $payload);
show("Registrar usuario -> HTTP $code", $code === 201);

// Buscar en usuarios pendientes
list($code, $body) = http('GET', $BASE_URL.'?action=pending_users');
$data = json_decode($body, true);
$idUser = null;
if (isset($data['users'])) {
    foreach ($data['users'] as $u) {
        if ($u['Correo'] === $email) $idUser = $u['id_Residente'];
    }
}
show("Usuario aparece en pendientes", $idUser !== null);

// Aprobar usuario
if ($idUser) {
    list($code, $body) = http('PUT', $BASE_URL.'?action=approve_user&id='.$idUser);
    show("Aprobar usuario -> HTTP $code", $code === 200);
}

// Login
list($code, $body) = http('POST', $BASE_URL.'?action=login', [
    'correo' => $email,
    'password' => 'Password123'
]);
$data = json_decode($body, true);
$idResidente = $data['user']['id'] ?? null;
show("Login usuario aprobado -> HTTP $code", $idResidente !== null);

// Registrar horas
if ($idResidente) {
    list($code, $body) = http('POST', $BASE_URL.'?action=add_hour', [
        'id_residente' => $idResidente,
        'fecha' => date('Y-m-d'),
        'cantidad' => 2,
        'descripcion' => 'Prueba web'
    ]);
    show("Registrar horas -> HTTP $code", $code === 201);
}

// Subir comprobante
if ($idResidente) {
    $tmp = tempnam(sys_get_temp_dir(), 'rcp');
    file_put_contents($tmp, "%PDF-1.4\nFANS test\n");
    $cfile = curl_file_create($tmp, 'application/pdf', 'comprobante_test.pdf');
    list($code, $body) = http('POST', $BASE_URL.'?action=upload_receipt', null, [
        'id_residente' => $idResidente,
        'tipo' => 'Recibo Web',
        'fecha' => date('Y-m-d'),
        'monto' => '100.00',
        'archivo' => $cfile
    ]);
    show("Subir comprobante -> HTTP $code", $code === 201);
}

echo "<hr><p><b>Tests finalizados.</b></p>";
