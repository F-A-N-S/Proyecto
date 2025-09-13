-- Seeds de ejemplo para pruebas (2ª Entrega)

-- Limpiar datos (cuidado en producción)
SET FOREIGN_KEY_CHECKS=0;
DELETE FROM Horas_Trabajo;
DELETE FROM Comprobantes_Pago;
DELETE FROM Residente;
DELETE FROM Administrador;
DELETE FROM Vivienda;
SET FOREIGN_KEY_CHECKS=1;

-- Viviendas
INSERT INTO Vivienda (id_Vivienda, Numero, Torre, Piso) VALUES
(1, 'A-101', 'A', '1'),
(2, 'B-204', 'B', '2');

-- Administradores
INSERT INTO Administrador (Nombre, Apellido, Correo, Telefono, Contrasena, Fecha_Ingreso) VALUES
('Admin', 'Principal', 'admin@fans.com', '099111111', '$2y$10$PGY2b1cK9f0mQjQj3PjvUuM3O0KQyJmV5GzRkQmN4aM1m8r0i3G9a', '2024-01-01'); -- pass: Admin1234

-- Residentes
INSERT INTO Residente (Nombre, Apellido, Cedula, Correo, Telefono, Fecha_Ingreso, id_Vivienda, Contrasena, estado_aprobacion) VALUES
('Ana', 'García', '4.123.456-7', 'ana@example.com', '091234567', '2024-03-01', 1, '$2y$10$kys2c8yq0N2mYv4yZ0G1eOqm0QbZl2b0G0QYVq9y1gkqJmWf8b5nG', TRUE),  -- pass: Password123
('Luis', 'Pérez', '5.987.654-3', 'luis@example.com', '098765432', '2024-04-10', 2, '$2y$10$kys2c8yq0N2mYv4yZ0G1eOqm0QbZl2b0G0QYVq9y1gkqJmWf8b5nG', FALSE); -- pass: Password123

-- Horas (Ana aprobada)
INSERT INTO Horas_Trabajo (id_Residente, Fecha, Cantidad, Descripcion) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 3.5, 'Limpieza de espacios comunes'),
(1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 2.0, 'Reunión de coordinación'),
(1, CURDATE(), 4.0, 'Mantenimiento de jardín');

-- Comprobantes (Ana)
INSERT INTO Comprobantes_Pago (Tipo, Fecha, Monto, Estado, Archivo, id_Residente) VALUES
('Recibo de Alquiler', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 12500.00, 'Pendiente', 'uploads/comprobantes/demo1.pdf', 1),
('Factura de Agua', DATE_SUB(CURDATE(), INTERVAL 20 DAY), 850.75, 'Aprobado', 'uploads/comprobantes/demo2.pdf', 1);
