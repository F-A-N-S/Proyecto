-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-09-2025 a las 23:53:33
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fans_cooperativa`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

CREATE TABLE `administrador` (
  `id_Administrador` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Usuario` varchar(50) NOT NULL,
  `Correo` varchar(120) DEFAULT NULL,
  `Contrasena` varchar(255) NOT NULL,
  `estado_aprobacion` tinyint(1) DEFAULT 0,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`id_Administrador`, `Nombre`, `Apellido`, `Usuario`, `Correo`, `Contrasena`, `estado_aprobacion`, `fecha_registro`) VALUES
(2, 'Admin', 'Principal', 'admin', 'admin@fans.com', '$2y$10$EIXfPhCzUR6uxxJe4uIihOr7BZWV4fpM9ZZF.mrmK/4d89eUhwLce', 1, '2025-09-12 18:43:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobante`
--

CREATE TABLE `comprobante` (
  `id_Comprobante` int(11) NOT NULL,
  `Tipo` varchar(100) NOT NULL,
  `Fecha` date NOT NULL,
  `Monto` decimal(10,2) DEFAULT NULL,
  `Estado` varchar(50) NOT NULL DEFAULT 'Pendiente',
  `Archivo` varchar(255) NOT NULL,
  `Fecha_Subida` datetime DEFAULT current_timestamp(),
  `id_Residente` int(11) NOT NULL,
  `id_Administrador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobantes_pago`
--

CREATE TABLE `comprobantes_pago` (
  `id_Comprobante` int(11) NOT NULL,
  `Tipo` varchar(100) NOT NULL,
  `Fecha` date NOT NULL,
  `Monto` decimal(10,2) DEFAULT NULL,
  `Estado` varchar(50) NOT NULL DEFAULT 'Pendiente',
  `Archivo` varchar(255) NOT NULL,
  `Fecha_Subida` datetime DEFAULT current_timestamp(),
  `id_Residente` int(11) NOT NULL,
  `id_Administrador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comprobantes_pago`
--

INSERT INTO `comprobantes_pago` (`id_Comprobante`, `Tipo`, `Fecha`, `Monto`, `Estado`, `Archivo`, `Fecha_Subida`, `id_Residente`, `id_Administrador`) VALUES
(1, 'Factura', '2025-09-12', 900.00, 'Pendiente', 'uploads/comprobantes/comp_1757702142_Repartido_pr__ctico__Funciones_compuestas__1_.pdf', '2025-09-12 15:35:42', 6, NULL),
(2, 'Factuara', '2025-09-12', 2232.00, 'Aprobado', 'uploads/comprobantes/comp_1757709519_Soprano_cute_kitten.jpeg', '2025-09-12 17:38:39', 7, NULL),
(3, 'Factura', '2025-09-12', 222.00, 'Aprobado', 'uploads/comprobantes/comp_1757709555_pichu.png', '2025-09-12 17:39:15', 7, NULL),
(4, 'F', '2025-09-12', 3232.00, 'Aprobado', 'uploads/comprobantes/comp_1757709581_Proyecto_de_Pasaje_de_Grado_-_2025_2.pdf', '2025-09-12 17:39:41', 7, NULL),
(5, 'Factura', '2025-09-12', 555.00, 'Pendiente', 'uploads/comprobantes/comp_1757709706_premium_photo-1681487178876-a1156952ec60.jpeg', '2025-09-12 17:41:46', 6, NULL),
(6, 'Factura', '2025-09-12', 345.00, 'Pendiente', 'uploads/comprobantes/comp_1757711405_Repartido_pr__ctico__Funciones_compuestas__1_.pdf', '2025-09-12 18:10:05', 6, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicados`
--

CREATE TABLE `comunicados` (
  `Id_Aviso` int(11) NOT NULL,
  `Titulo` varchar(255) NOT NULL,
  `Fecha` datetime DEFAULT current_timestamp(),
  `Destinatario` varchar(255) DEFAULT NULL,
  `Contenido` text DEFAULT NULL,
  `id_Administrador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horas_trabajo`
--

CREATE TABLE `horas_trabajo` (
  `id_Hora` int(11) NOT NULL,
  `id_Residente` int(11) NOT NULL,
  `Fecha` date NOT NULL,
  `Cantidad` decimal(5,2) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Fecha_Registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horas_trabajo`
--

INSERT INTO `horas_trabajo` (`id_Hora`, `id_Residente`, `Fecha`, `Cantidad`, `Descripcion`, `Fecha_Registro`) VALUES
(1, 6, '2025-09-12', 67.00, 'Limpieza', '2025-09-12 15:35:31'),
(2, 7, '2025-09-12', 43.00, 'Limpieza', '2025-09-12 17:38:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `Id_Pagos` int(11) NOT NULL,
  `Monto` decimal(10,2) NOT NULL,
  `Medio` varchar(50) DEFAULT NULL,
  `Fecha` datetime DEFAULT current_timestamp(),
  `Estado` varchar(50) DEFAULT NULL,
  `id_Residente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulante`
--

CREATE TABLE `postulante` (
  `id_Postulante` int(11) NOT NULL,
  `Usuario` varchar(50) NOT NULL,
  `Contrasena` varchar(255) NOT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Apellido` varchar(100) DEFAULT NULL,
  `Correo` varchar(100) DEFAULT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Fecha_Registro` datetime DEFAULT current_timestamp(),
  `id_Vivienda` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reclamos`
--

CREATE TABLE `reclamos` (
  `Id_Reclamo` int(11) NOT NULL,
  `Descripcion` text NOT NULL,
  `Fecha` datetime DEFAULT current_timestamp(),
  `Prioridad` varchar(50) DEFAULT NULL,
  `Estado` varchar(50) DEFAULT NULL,
  `id_Residente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `residente`
--

CREATE TABLE `residente` (
  `id_Residente` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Cedula` varchar(20) DEFAULT NULL,
  `Correo` varchar(100) NOT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Fecha_Ingreso` date NOT NULL,
  `id_Vivienda` int(11) DEFAULT NULL,
  `Contrasena` varchar(255) NOT NULL,
  `estado_aprobacion` tinyint(1) DEFAULT 0,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `Usuario` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `residente`
--

INSERT INTO `residente` (`id_Residente`, `Nombre`, `Apellido`, `Cedula`, `Correo`, `Telefono`, `Fecha_Ingreso`, `id_Vivienda`, `Contrasena`, `estado_aprobacion`, `fecha_registro`, `Usuario`) VALUES
(6, 'Santiago', 'mora', '1234654223', 'matateporte@gmail.com', '12312312', '2132-03-12', NULL, '$2y$10$/N8kHg9fRTwqfJgr0BYTKeFkBhNZ5DeEtchl7PGHJ71ggESf6U03G', 1, '2025-09-12 15:04:08', 'matateporte@gmail.com'),
(7, 'Santiago', 'mora', '092392', 'santiago@gmail.com', '3423423423', '2025-09-12', NULL, '$2y$10$BSlsWfyUKN1Rg0/GPyTdIeHPhwH2cvZC2HB60tWnEmxcAH6JYbEu2', 1, '2025-09-12 17:37:43', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vivienda`
--

CREATE TABLE `vivienda` (
  `id_Vivienda` int(11) NOT NULL,
  `Numero` varchar(20) NOT NULL,
  `Direccion` varchar(255) NOT NULL,
  `Tipo` varchar(50) DEFAULT NULL,
  `Estado` varchar(50) DEFAULT NULL,
  `id_Administrador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_Administrador`),
  ADD UNIQUE KEY `Usuario` (`Usuario`),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- Indices de la tabla `comprobante`
--
ALTER TABLE `comprobante`
  ADD PRIMARY KEY (`id_Comprobante`),
  ADD KEY `id_Residente` (`id_Residente`),
  ADD KEY `id_Administrador` (`id_Administrador`);

--
-- Indices de la tabla `comprobantes_pago`
--
ALTER TABLE `comprobantes_pago`
  ADD PRIMARY KEY (`id_Comprobante`),
  ADD KEY `fk_comp_res` (`id_Residente`),
  ADD KEY `fk_comp_admin` (`id_Administrador`);

--
-- Indices de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`Id_Aviso`),
  ADD KEY `id_Administrador` (`id_Administrador`);

--
-- Indices de la tabla `horas_trabajo`
--
ALTER TABLE `horas_trabajo`
  ADD PRIMARY KEY (`id_Hora`),
  ADD KEY `fk_horas_res` (`id_Residente`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`Id_Pagos`),
  ADD KEY `id_Residente` (`id_Residente`);

--
-- Indices de la tabla `postulante`
--
ALTER TABLE `postulante`
  ADD PRIMARY KEY (`id_Postulante`),
  ADD UNIQUE KEY `Usuario` (`Usuario`),
  ADD UNIQUE KEY `Correo` (`Correo`),
  ADD KEY `id_Vivienda` (`id_Vivienda`);

--
-- Indices de la tabla `reclamos`
--
ALTER TABLE `reclamos`
  ADD PRIMARY KEY (`Id_Reclamo`),
  ADD KEY `id_Residente` (`id_Residente`);

--
-- Indices de la tabla `residente`
--
ALTER TABLE `residente`
  ADD PRIMARY KEY (`id_Residente`),
  ADD UNIQUE KEY `Correo` (`Correo`),
  ADD UNIQUE KEY `Cedula` (`Cedula`),
  ADD UNIQUE KEY `Usuario` (`Usuario`),
  ADD KEY `id_Vivienda` (`id_Vivienda`);

--
-- Indices de la tabla `vivienda`
--
ALTER TABLE `vivienda`
  ADD PRIMARY KEY (`id_Vivienda`),
  ADD UNIQUE KEY `Numero` (`Numero`),
  ADD KEY `id_Administrador` (`id_Administrador`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id_Administrador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `comprobante`
--
ALTER TABLE `comprobante`
  MODIFY `id_Comprobante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `comprobantes_pago`
--
ALTER TABLE `comprobantes_pago`
  MODIFY `id_Comprobante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `Id_Aviso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horas_trabajo`
--
ALTER TABLE `horas_trabajo`
  MODIFY `id_Hora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `Id_Pagos` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `postulante`
--
ALTER TABLE `postulante`
  MODIFY `id_Postulante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reclamos`
--
ALTER TABLE `reclamos`
  MODIFY `Id_Reclamo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `residente`
--
ALTER TABLE `residente`
  MODIFY `id_Residente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `vivienda`
--
ALTER TABLE `vivienda`
  MODIFY `id_Vivienda` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comprobante`
--
ALTER TABLE `comprobante`
  ADD CONSTRAINT `comprobante_ibfk_1` FOREIGN KEY (`id_Residente`) REFERENCES `residente` (`id_Residente`) ON DELETE CASCADE,
  ADD CONSTRAINT `comprobante_ibfk_2` FOREIGN KEY (`id_Administrador`) REFERENCES `administrador` (`id_Administrador`) ON DELETE SET NULL;

--
-- Filtros para la tabla `comprobantes_pago`
--
ALTER TABLE `comprobantes_pago`
  ADD CONSTRAINT `fk_comp_admin` FOREIGN KEY (`id_Administrador`) REFERENCES `administrador` (`id_Administrador`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_comp_res` FOREIGN KEY (`id_Residente`) REFERENCES `residente` (`id_Residente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD CONSTRAINT `comunicados_ibfk_1` FOREIGN KEY (`id_Administrador`) REFERENCES `administrador` (`id_Administrador`) ON DELETE CASCADE;

--
-- Filtros para la tabla `horas_trabajo`
--
ALTER TABLE `horas_trabajo`
  ADD CONSTRAINT `fk_horas_res` FOREIGN KEY (`id_Residente`) REFERENCES `residente` (`id_Residente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_Residente`) REFERENCES `residente` (`id_Residente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `postulante`
--
ALTER TABLE `postulante`
  ADD CONSTRAINT `postulante_ibfk_1` FOREIGN KEY (`id_Vivienda`) REFERENCES `vivienda` (`id_Vivienda`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reclamos`
--
ALTER TABLE `reclamos`
  ADD CONSTRAINT `reclamos_ibfk_1` FOREIGN KEY (`id_Residente`) REFERENCES `residente` (`id_Residente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `residente`
--
ALTER TABLE `residente`
  ADD CONSTRAINT `fk_res_vivienda` FOREIGN KEY (`id_Vivienda`) REFERENCES `vivienda` (`id_Vivienda`) ON DELETE SET NULL,
  ADD CONSTRAINT `residente_ibfk_1` FOREIGN KEY (`id_Vivienda`) REFERENCES `vivienda` (`id_Vivienda`) ON DELETE SET NULL;

--
-- Filtros para la tabla `vivienda`
--
ALTER TABLE `vivienda`
  ADD CONSTRAINT `vivienda_ibfk_1` FOREIGN KEY (`id_Administrador`) REFERENCES `administrador` (`id_Administrador`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
