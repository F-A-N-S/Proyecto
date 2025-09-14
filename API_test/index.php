<?php
require __DIR__ . '/curl_helper.php';

/* === CONFIG (ADAPTADO A TU PROYECTO) ===
   Ajusta solo API_BASE si tu ruta es distinta.
*/
// Construye la URL absoluta a /frontend/api/api.php
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'];                         // ej: localhost
$base   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');  // ej: /fans.../API_test
$API_BASE = $scheme.'://'.$host.preg_replace('#/API_test/?$#','/frontend/api/api.php', $base);

// Si querés forzar manualmente, descomentá la siguiente línea:
// $API_BASE = 'http://localhost/fans-cooperativa_2da_entrega_responsive_v3/frontend/api/api.php';
 // <-- tu api.php real
$ADMIN_EMAIL = 'admin@fans.com';       // viene en BaseDatos/sample_data.sql
$ADMIN_PASS  = 'Admin1234';            // contraseña de ejemplo del seed

/* helpers */
function okish($s){ return $s>=200 && $s<500; }
function action_url($base, $action){ return rtrim($base,'/').'?action='.$action; }
function GET($a, $q=[]){ return $a.(strpos($a,'?')!==false?'&':'?').http_build_query($q); }

$cookie = tempnam(sys_get_temp_dir(), 'fans_cookie_');
$tests = [];
function step($name, $fn){ global $tests; try{$out=$fn(); $tests[]=['name'=>$name,'status'=>'OK','out'=>$out];}
catch(Throwable $e){$tests[]=['name'=>$name,'status'=>'FAIL','err'=>$e->getMessage()];}}

/* estado compartido */
$ctx = [
  'new_email' => 'test_'.date('Ymd_His').'@example.com',
  'new_pass'  => 'P4ssw0rd!',
  'new_user_id' => null,
  'resident_id' => null,
  'admin_id' => null,
  'receipt_id' => null,
  'receipt_path' => null,
];

/* 0) Ping */
step('Ping API', function() use($API_BASE){
  [$s] = api_call('GET', $API_BASE);
  if (!okish($s)) throw new Exception("API no responde: HTTP $s");
  return ['status'=>$s];
});

/* 1) Login ADMIN (para aprobar usuarios y comprobantes) */
step('Login admin', function() use($API_BASE,$ADMIN_EMAIL,$ADMIN_PASS,$cookie,&$ctx){
  $u = action_url($API_BASE,'login');
  $payload = json_encode(['correo'=>$ADMIN_EMAIL,'password'=>$ADMIN_PASS], JSON_UNESCAPED_UNICODE);
  [$s,$b] = api_call('POST', $u, $payload, ['Content-Type: application/json'], $cookie);
  if ($s<200 || $s>=300) throw new Exception("Login admin HTTP $s ".(is_string($b)?$b:json_encode($b)));
  if (!is_array($b) || !isset($b['user']['id'])) throw new Exception('Respuesta de admin sin user.id');
  $ctx['admin_id'] = $b['user']['id'];
  return ['id'=>$ctx['admin_id']];
});

/* 2) Registrar residente (queda PENDIENTE por diseño de tu API) */
step('Registro residente (pendiente)', function() use($API_BASE,$cookie,&$ctx){
  $u = action_url($API_BASE,'register');
  $data = [
    'nombre'=>'TEST',
    'apellido'=>'AUTO',
    'correo'=>$ctx['new_email'],
    'password'=>$ctx['new_pass'],
    'fecha_ingreso'=>date('Y-m-d'),
  ];
  [$s,$b] = api_call('POST', $u, json_encode($data,JSON_UNESCAPED_UNICODE), ['Content-Type: application/json'], $cookie);
  if ($s!==201 && $s!==409) throw new Exception("Registro HTTP $s ".(is_string($b)?$b:json_encode($b)));
  return $b;
});

/* 3) Buscar en pendientes y APROBAR usuario nuevo (PUT ?id=) */
step('Aprobar usuario recién registrado', function() use($API_BASE,$cookie,&$ctx){
  // listar pendientes
  $u = action_url($API_BASE,'pending_users');
  [$s,$b] = api_call('GET', $u, null, [], $cookie);
  if ($s<200 || $s>=300) throw new Exception("pending_users HTTP $s");
  if (!isset($b['users'])) throw new Exception('Respuesta sin users');
  $match = null;
  foreach ($b['users'] as $u) {
    if (isset($u['Correo']) && strtolower($u['Correo'])===strtolower($ctx['new_email'])) { $match=$u; break; }
  }
  if (!$match) throw new Exception('No aparece en pendientes (chequea seed/DB)');
  $ctx['new_user_id'] = $match['id_Residente'] ?? null;
  if (!$ctx['new_user_id']) throw new Exception('Pendiente sin id_Residente');

  // aprobar
  $u2 = GET(action_url($API_BASE,'approve_user'), ['id'=>$ctx['new_user_id']]);
  [$s2,$b2] = api_call('PUT', $u2, null, [], $cookie);
  if ($s2<200 || $s2>=300) throw new Exception("approve_user HTTP $s2 ".(is_string($b2)?$b2:json_encode($b2)));
  return ['user_id'=>$ctx['new_user_id']];
});

/* 4) Login RESIDENTE (ya aprobado) */
step('Login residente', function() use($API_BASE,$cookie,&$ctx){
  $u = action_url($API_BASE,'login');
  [$s,$b] = api_call('POST', $u, json_encode(['correo'=>$ctx['new_email'],'password'=>$ctx['new_pass']],JSON_UNESCAPED_UNICODE), ['Content-Type: application/json'], $cookie);
  if ($s<200 || $s>=300) throw new Exception("Login residente HTTP $s ".(is_string($b)?$b:json_encode($b)));
  if (!isset($b['user']['id'])) throw new Exception('Respuesta login sin user.id');
  $ctx['resident_id'] = $b['user']['id'];
  return ['resident_id'=>$ctx['resident_id']];
});

/* 5) Registrar horas (POST JSON) */
step('Registrar horas', function() use($API_BASE,$cookie,&$ctx){
  $u = action_url($API_BASE,'add_hour');
  $data = [
    'id_residente'=>$ctx['resident_id'],
    'fecha'=>date('Y-m-d'),
    'cantidad'=>3.0,
    'descripcion'=>'Horas test automáticas'
  ];
  [$s,$b] = api_call('POST', $u, json_encode($data,JSON_UNESCAPED_UNICODE), ['Content-Type: application/json'], $cookie);
  if ($s<200 || $s>=300) throw new Exception("add_hour HTTP $s ".(is_string($b)?$b:json_encode($b)));
  return $b;
});

/* 6) Listar horas (GET id_residente) */
step('Listar horas', function() use($API_BASE,$cookie,&$ctx){
  $u = GET(action_url($API_BASE,'list_hours'), ['id_residente'=>$ctx['resident_id']]);
  [$s,$b] = api_call('GET', $u, null, [], $cookie);
  if ($s<200 || $s>=300) throw new Exception("list_hours HTTP $s");
  $txt = json_encode($b, JSON_UNESCAPED_UNICODE);
  if (stripos($txt,'Horas test automáticas')===false) throw new Exception('No aparece la entrada creada');
  return ['ok'=>true, 'items'=> (isset($b['hours'])? count($b['hours']): 'n/d')];
});

/* 7) Subir comprobante (multipart: archivo + campos requeridos) */
/* 7) Subir comprobante (multipart: archivo PNG + campos requeridos) */
step('Subir comprobante', function() use($API_BASE,$cookie,&$ctx){
  // PNG 1x1 válido (base64)
  $pngB64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=';
  $tmp = tempnam(sys_get_temp_dir(),'comp_');
  file_put_contents($tmp, base64_decode($pngB64));

  $u = action_url($API_BASE,'upload_receipt');
  $data = [
    'id_residente' => $ctx['resident_id'],
    'tipo'         => 'Recibo de Alquiler',   // ajusta si tu API espera otro texto
    'fecha'        => date('Y-m-d'),
    'monto'        => '1234.56'
  ];

  [$s,$b] = api_call(
    'POST',
    $u,
    $data,
    [],                    // headers extra
    $cookie,
    [ 'archivo' => [       // <- NOMBRE DEL CAMPO FILE EN TU API
        'path' => $tmp,
        'type' => 'image/png',
        'name' => 'comprobante.png'
      ]
    ]
  );

  if ($s<200 || $s>=300) throw new Exception("upload_receipt HTTP $s ".(is_string($b)?$b:json_encode($b)));

  // Guardamos la ruta/nombre que devuelva tu API (si lo hace)
  if (is_array($b) && isset($b['archivo'])) $ctx['receipt_path'] = $b['archivo'];
  return $b;
});


/* 8) Listar comprobantes (GET id_residente) y capturar id */
step('Listar comprobantes', function() use($API_BASE,$cookie,&$ctx){
  $u = GET(action_url($API_BASE,'list_receipts'), ['id_residente'=>$ctx['resident_id']]);
  [$s,$b] = api_call('GET', $u, null, [], $cookie);
  if ($s<200 || $s>=300) throw new Exception("list_receipts HTTP $s");
  if (!isset($b['receipts'])) throw new Exception('Respuesta sin receipts');
  $found = null;
  foreach ($b['receipts'] as $r) {
    if ($ctx['receipt_path'] && isset($r['Archivo']) && $r['Archivo']===$ctx['receipt_path']) { $found=$r; break; }
  }
  if (!$found) $found = $b['receipts'][0] ?? null;
  if (!$found || !isset($found['id_Comprobante'])) throw new Exception('No pude determinar id_Comprobante');
  $ctx['receipt_id'] = $found['id_Comprobante'];
  return ['receipt_id'=>$ctx['receipt_id']];
});

/* 9) (Opcional) Aprobar comprobante (PUT ?id=) */
step('Aprobar comprobante', function() use($API_BASE,$cookie,&$ctx){
  $u = GET(action_url($API_BASE,'approve_receipt'), ['id'=>$ctx['receipt_id']]);
  [$s,$b] = api_call('PUT', $u, null, [], $cookie);
  if ($s<200 || $s>=300) throw new Exception("approve_receipt HTTP $s ".(is_string($b)?$b:json_encode($b)));
  return $b;
});

/* Render simple */
?><!doctype html><meta charset="utf-8">
<title>API_test · FANS</title>
<style>
  body{font-family:system-ui;margin:24px;background:#0b0b0b;color:#e5e7eb}
  .ok{color:#22c55e}.fail{color:#ef4444}
  pre{white-space:pre-wrap;background:#111827;border:1px solid #1f2937;padding:8px;border-radius:8px}
  .card{border:1px solid #1f2937;border-radius:12px;padding:12px;margin:10px 0;background:#0f172a}
</style>
<h1>TEST</h1>
<?php foreach($tests as $t): ?>
  <div class="card">
    <strong class="<?= $t['status']==='OK'?'ok':'fail'?>">■ <?=$t['status']?></strong>
    — <?=htmlspecialchars($t['name'])?><br/>
    <?php if(isset($t['err'])): ?><pre><?=htmlspecialchars($t['err'])?></pre><?php endif; ?>
    <?php if(isset($t['out'])): ?><pre><?=htmlspecialchars(is_string($t['out'])?$t['out']:json_encode($t['out'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))?></pre><?php endif; ?>
  </div>
<?php endforeach; ?>
