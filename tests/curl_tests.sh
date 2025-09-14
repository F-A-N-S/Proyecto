#!/usr/bin/env bash
set -e
BASE_URL="http://localhost/fans-cooperativa/frontend/api/api.php"

echo "== Registro usuario =="
curl -s -X POST "$BASE_URL?action=register" -H "Content-Type: application/json" -d '{"nombre":"Test","apellido":"User","cedula":"test-ced","correo":"test@example.com","telefono":"099","fecha_ingreso":"2025-08-01","password":"Password123"}' | jq .

echo "== Usuarios pendientes =="
curl -s "$BASE_URL?action=pending_users" | jq .

echo "== Aprobar usuario id=1 (ajusta) =="
curl -s -X PUT "$BASE_URL?action=approve_user&id=1" | jq .

echo "== Login =="
curl -s -X POST "$BASE_URL?action=login" -H "Content-Type: application/json" -d '{"correo":"test@example.com","password":"Password123"}' | jq .

echo "== Agregar horas =="
curl -s -X POST "$BASE_URL?action=add_hour" -H "Content-Type: application/json" -d '{"id_residente":1,"fecha":"2025-08-10","cantidad":3.5,"descripcion":"Tareas comunes"}' | jq .

echo "== Listar horas =="
curl -s "$BASE_URL?action=list_hours&id_residente=1" | jq .

echo "== Listar comprobantes pendientes =="
curl -s "$BASE_URL?action=list_receipts&estado=Pendiente" | jq .
