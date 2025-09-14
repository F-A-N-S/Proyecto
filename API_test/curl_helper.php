<?php
function api_call($method, $url, $data = null, $headers = [], $cookieFile = null, $fileFields = []) {
  $ch = curl_init();
  $opts = [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => strtoupper($method),
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_TIMEOUT => 30,
  ];
  if ($cookieFile) {
    $opts[CURLOPT_COOKIEJAR] = $cookieFile;
    $opts[CURLOPT_COOKIEFILE] = $cookieFile;
  }

  if ($fileFields) {                       // multipart (subida de archivo)
    foreach ($fileFields as $k => $v) {
      $data[$k] = new CURLFile($v['path'], $v['type'] ?? 'application/octet-stream', $v['name'] ?? basename($v['path']));
    }
    $opts[CURLOPT_POSTFIELDS] = $data ?: [];
  } elseif ($data !== null) {              // x-www-form-urlencoded o JSON
    if (is_string($data)) {                // ya viene JSON string
      $opts[CURLOPT_POSTFIELDS] = $data;
      $headers[] = 'Content-Type: application/json';
    } else {
      $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
      $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }
  }

  if ($headers) $opts[CURLOPT_HTTPHEADER] = $headers;

  curl_setopt_array($ch, $opts);
  $resp = curl_exec($ch);
  $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $rawHeaders = substr($resp, 0, $headerSize);
  $body = substr($resp, $headerSize);
  curl_close($ch);

  $json = null;
  $trim = ltrim($body);
  if ($trim !== '' && ($trim[0] === '{' || $trim[0] === '[')) {
    $json = json_decode($body, true);
  }
  return [$status, $json ?? $body, $rawHeaders];
}
