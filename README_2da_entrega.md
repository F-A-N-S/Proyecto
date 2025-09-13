# F.A.N.S Cooperativa – 2ª Entrega

Incluye:
- API de Cooperativa: **Horas de Trabajo** y **Comprobantes de Pago**
- Frontend: formularios de carga de horas y de subida de comprobantes
- Backoffice: listado de comprobantes **pendientes** y acción de **aprobar**
- Tests: script `curl_tests.sh` + colección de Postman
- Docker: `docker-compose.yml` para levantar MySQL y Apache+PHP sirviendo el frontend

## Levantar con Docker
```bash
docker compose up
```
- MySQL: `localhost:3306` (user: root, pass: root, DB: fans_cooperativa)
- Frontend + API (php): `http://localhost:8080/`
  - API base: `http://localhost:8080/api/api.php`

> El contenedor `db` ejecuta `BaseDatos/DDL.txt` y `fans_cooperativa.sql` automáticamente.

## Endpoints nuevos
- **POST** `?action=add_hour` → `{ id_residente, fecha, cantidad, descripcion? }`
- **GET** `?action=list_hours&id_residente=...`
- **POST** `?action=upload_receipt` → `multipart/form-data` (`id_residente`, `tipo`, `fecha`, `monto?`, `archivo`)
- **GET** `?action=list_receipts&estado=Pendiente|Aprobado|...&id_residente=?`
- **PUT** `?action=approve_receipt&id=...`

## Vistas nuevas
- `dashboard.html` → secciones **Registro de Horas** y **Comprobantes**, usando `js/hours.js` y `js/receipts.js`.
- `backoffice.html` → sección **Comprobantes pendientes** con botón **Aprobar**.

## Datos de prueba
- Podés cargar usuarios y luego:
  - Registrar horas desde el dashboard.
  - Subir un comprobante (PDF/JPG/PNG); quedará en `frontend/uploads/comprobantes/`.
  - Aprobar el comprobante desde Backoffice.


## Validaciones agregadas (backend)
- **Registro**: email válido, contraseña fuerte (>=8 con letras y números), teléfono/cédula con formato básico, fecha válida.
- **Horas**: fecha válida no futura; cantidad numérica entre 0 y 24.
- **Comprobantes**: archivo permitido (PDF/JPG/PNG), **MIME verificado**, tamaño <= **5MB**, fecha válida no futura.

## Seeds
- Archivo: `BaseDatos/sample_data.sql`
- Incluye: 1 admin, 2 residentes (1 aprobado), 3 registros de horas, 2 comprobantes (pendiente y aprobado).
- Contraseñas:
  - Admin: **Admin1234**
  - Usuarios (Ana / Luis): **Password123**
