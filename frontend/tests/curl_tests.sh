#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${FANS_API:-http://localhost/fans-cooperativa_2da_entrega_responsive_v3/frontend/api/api.php}"
JQ="${JQ:-jq}"

banner(){ echo -e "\n== $* =="; }

email="test_api_$(date +%Y%m%d_%H%M%S)_$RANDOM@example.com"

banner "Registro usuario ($email)"
curl -s -X POST "$BASE_URL?action=register" \
  -H "Content-Type: application/json" \
  -d "{\"nombre\":\"Test\",\"apellido\":\"API\",\"correo\":\"$email\",\"password\":\"Password123\",\"fecha_ingreso\":\"$(date +%F)\",\"cedula\":\"4.123.456-7\",\"telefono\":\"091234567\"}" | $JQ .

banner "Usuarios pendientes"
curl -s "$BASE_URL?action=pending_users" | $JQ .

uid=$(curl -s "$BASE_URL?action=pending_users" | $JQ -r --arg email "$email" '.users[] | select(.Correo==$email) | .id_Residente' | head -n1)

banner "Aprobar usuario id=$uid"
curl -s -X PUT "$BASE_URL?action=approve_user&id=$uid" | $JQ .

banner "Login"
curl -s -X POST "$BASE_URL?action=login" -H "Content-Type: application/json" \
  -d "{\"correo\":\"$email\",\"password\":\"Password123\"}" | $JQ .

rid=$(curl -s -X POST "$BASE_URL?action=login" -H "Content-Type: application/json" \
  -d "{\"correo\":\"$email\",\"password\":\"Password123\"}" | $JQ -r '.user.id')

banner "Agregar horas"
curl -s -X POST "$BASE_URL?action=add_hour" -H "Content-Type: application/json" \
  -d "{\"id_residente\":$rid,\"fecha\":\"$(date +%F)\",\"cantidad\":2.5,\"descripcion\":\"Prueba automática\"}" | $JQ .

banner "Listar horas"
curl -s "$BASE_URL?action=list_hours&id_residente=$rid" | $JQ .

banner "Subir comprobante"
tmp="/tmp/rcp_$$.pdf"; printf "%%PDF-1.4\nFANS test\n" > "$tmp"
curl -s -X POST "$BASE_URL?action=upload_receipt" \
  -F "id_residente=$rid" -F "tipo=Recibo de Prueba" -F "fecha=$(date +%F)" -F "monto=123.45" \
  -F "archivo=@$tmp;type=application/pdf;filename=comprobante_test.pdf" | $JQ .

banner "Listar comprobantes (id_residente)"
curl -s "$BASE_URL?action=list_receipts&id_residente=$rid" | $JQ .

cid=$(curl -s "$BASE_URL?action=list_receipts&id_residente=$rid" | $JQ -r '(.receipts // .items) | .[0].id_Comprobante')

banner "Aprobar comprobante id=$cid"
curl -s -X PUT "$BASE_URL?action=approve_receipt&id=$cid" -H "Content-Type: application/json" \
  -d "{\"id_admin\":1}" | $JQ .
