-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-06-2026 a las 18:21:41
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
-- Base de datos: `mediflow`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivo`
--

DROP TABLE IF EXISTS `archivo`;
CREATE TABLE `archivo` (
  `id_archivo` int(11) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `ruta` varchar(255) NOT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `archivo`
--

INSERT INTO `archivo` (`id_archivo`, `id_solicitud`, `nombre`, `tipo`, `ruta`, `fecha_subida`) VALUES
(1, 8, 'Captura de pantalla 2026-05-28 194703.png', 'png', 'estudio_6a19fba59d6b8_0.png', '2026-05-29 17:48:37'),
(2, 8, 'Tp2.pdf', 'pdf', 'estudio_6a19fba59e17b_1.pdf', '2026-05-29 17:48:37'),
(3, 9, 'Unidad4.pdf', 'pdf', 'estudio_6a1a1c1658677_0.pdf', '2026-05-29 20:07:02'),
(4, 13, 'Captura de pantalla 2026-06-17 101001.png', 'png', 'estudio_6a3314b12eaba_0.png', '2026-06-17 18:42:09'),
(5, 13, 'Trabajo Práctico 3.pdf', 'pdf', 'estudio_6a33159fc3bab_0.pdf', '2026-06-17 18:46:07'),
(6, 14, 'Captura de pantalla 2026-06-17 184353.png', 'png', 'estudio_6a331876cc866_0.png', '2026-06-17 18:58:14'),
(7, 14, 'Practico4_Redes2.pdf', 'pdf', 'estudio_6a331876cdda6_1.pdf', '2026-06-17 18:58:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditor`
--

DROP TABLE IF EXISTS `auditor`;
CREATE TABLE `auditor` (
  `id_auditor` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `sector` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditor`
--

INSERT INTO `auditor` (`id_auditor`, `id_usuario`, `sector`) VALUES
(1, 5, 'Auditoría Médica Central');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluacion`
--

DROP TABLE IF EXISTS `evaluacion`;
CREATE TABLE `evaluacion` (
  `id_evaluacion` int(11) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `id_auditor` int(11) NOT NULL,
  `estado_nuevo` varchar(50) NOT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `evaluacion`
--

INSERT INTO `evaluacion` (`id_evaluacion`, `id_solicitud`, `id_auditor`, `estado_nuevo`, `observaciones`) VALUES
(1, 1, 1, 'observada', 'Documentación correcta, práctica autorizada.'),
(4, 8, 1, 'observada', 'La foto del DNI adjunto está borrosa y no permite verificar la identidad del paciente.'),
(5, 3, 1, 'observada', 'La firma del médico en la receta adjunta es ilegible. Por favor, subir una copia más nítida.'),
(6, 9, 1, 'aprobada', 'Práctica autorizada conforme a la cobertura del plan PMO.'),
(7, 6, 1, 'rechazada', 'Se rechaza ya que agoto cupo mensual para dicha prestacion.'),
(8, 8, 1, 'aprobada', ''),
(9, 10, 1, 'rechazada', 'No adjunta examen medico'),
(10, 11, 1, 'observada', 'Observacion, corregir por favor'),
(11, 13, 1, 'observada', 'documento mal cargado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medico`
--

DROP TABLE IF EXISTS `medico`;
CREATE TABLE `medico` (
  `id_medico` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `matricula` varchar(50) NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `medico`
--

INSERT INTO `medico` (`id_medico`, `id_usuario`, `matricula`, `especialidad`) VALUES
(1, 2, 'MN-112233', 'Medicina General'),
(2, 6, 'MN-445566', 'Traumatología');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente`
--

DROP TABLE IF EXISTS `paciente`;
CREATE TABLE `paciente` (
  `id_paciente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `plan` varchar(50) NOT NULL,
  `nro_afiliado` varchar(50) NOT NULL,
  `fecha_alta` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paciente`
--

INSERT INTO `paciente` (`id_paciente`, `nombre`, `apellido`, `dni`, `fecha_nacimiento`, `email`, `telefono`, `plan`, `nro_afiliado`, `fecha_alta`) VALUES
(3, 'Micaela', 'Le Donne', '46866891', '2006-03-13', 'micaledonne@gmail.com', '2617158502', 'PMO Inicial', 'F-00001-01', '2026-05-19 16:26:44'),
(5, 'Matias', 'Gomez', '46959611', '2006-06-22', 'MatiG@gmail.com', '2614556677', 'Plenitud 200', 'F-66052-02', '2026-05-19 21:36:43'),
(6, 'Pedro', 'Martin', '46555790', '2006-05-21', 'pedromartin@gmail.com', '2615889900', 'Plenitud 200', 'F-77752-4', '2026-05-21 18:13:44'),
(7, 'Julieta', 'Lusch', '47718689', '2005-03-13', 'julietalussh@gmail.com', '2613221144', 'Plenitud 200', 'F-77753-5', '2026-05-21 18:14:58'),
(16, 'Franco', 'Bazán', '42793521', '2000-07-23', 'bazan5717@gmail.com', '02616946546', 'MediPro', 'F-00008-01', '2026-06-16 22:32:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso`
--

DROP TABLE IF EXISTS `permiso`;
CREATE TABLE `permiso` (
  `id_permiso` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permiso`
--

INSERT INTO `permiso` (`id_permiso`, `nombre`, `descripcion`) VALUES
(1, 'crear_solicitud', 'Crear solicitudes médicas'),
(2, 'consultar_solicitud', 'Consultar estado de solicitudes'),
(3, 'aprobar_solicitud', 'Aprobar solicitudes'),
(4, 'rechazar_solicitud', 'Rechazar solicitudes'),
(5, 'asignar_prioridad', 'Asignar prioridad'),
(6, 'gestionar_usuarios', 'Administrar usuarios del sistema');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

DROP TABLE IF EXISTS `planes`;
CREATE TABLE `planes` (
  `id_plan` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id_plan`, `nombre`) VALUES
(1, 'PMO inicial'),
(2, 'Plenitud200'),
(3, 'MediPro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `practica`
--

DROP TABLE IF EXISTS `practica`;
CREATE TABLE `practica` (
  `id_practica` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `practica`
--

INSERT INTO `practica` (`id_practica`, `nombre`, `descripcion`) VALUES
(1, 'Resonancia Magnética', 'RMN completa'),
(2, 'Tomografía', 'TAC con contraste'),
(3, 'Resonancia Magnética', NULL),
(4, 'Ecodoppler de Tiroides', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

DROP TABLE IF EXISTS `rol`;
CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre`) VALUES
(4, 'administrador'),
(5, 'auditor'),
(1, 'medico'),
(2, 'paciente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud`
--

DROP TABLE IF EXISTS `solicitud`;
CREATE TABLE `solicitud` (
  `id_solicitud` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `id_practica` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `estado` enum('pendiente','aprobada','rechazada','observada') DEFAULT 'pendiente',
  `prioridad` enum('alta','media','baja') DEFAULT 'media',
  `diagnostico` text DEFAULT NULL,
  `ruta_archivo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitud`
--

INSERT INTO `solicitud` (`id_solicitud`, `id_paciente`, `id_medico`, `id_practica`, `fecha`, `estado`, `prioridad`, `diagnostico`, `ruta_archivo`) VALUES
(1, 3, 1, 3, '2026-05-19 00:00:00', 'aprobada', 'media', 'Sospecha de desgarro meniscal', NULL),
(3, 5, 6, 2, '2026-05-19 00:00:00', 'observada', 'alta', 'Traumatismo de tobillo derecho por caída', 'receta_6a0cdc9b9c7c6.png'),
(4, 7, 6, 2, '2026-05-21 00:00:00', 'aprobada', 'baja', 'Control post-operatorio de ligamentos cruzados', NULL),
(6, 7, 2, 2, '2026-05-21 00:00:00', 'rechazada', 'media', 'Evaluación de dolor crónico en rodilla', NULL),
(7, 3, 6, 2, '2026-05-21 00:00:00', 'pendiente', 'alta', 'Cefalea tensional recurrente', NULL),
(8, 7, 2, 3, '2026-06-17 00:00:00', 'aprobada', 'media', 'Dolor lumbar agudo', NULL),
(9, 3, 6, 2, '2026-05-30 00:00:00', 'aprobada', 'media', 'Seguimiento de nódulo tiroideo', NULL),
(10, 5, 2, 2, '2026-06-06 00:00:00', 'rechazada', 'media', 'Traumatismo cerrado de tórax', NULL),
(11, 16, 2, 4, '2026-06-17 00:00:00', 'pendiente', 'alta', 'Paciente en estado critico, se solicita con urgencia dicho estudio.\r\nAdjunto RX.\r\nSe adjunto estudio solicitado', NULL),
(12, 16, 2, 1, '2026-06-17 00:00:00', 'pendiente', 'baja', 'A', NULL),
(13, 16, 2, 4, '2026-06-17 00:00:00', 'pendiente', 'media', 'Control\r\nse adjunta nuevo archivo', NULL),
(14, 6, 2, 2, '2026-06-17 00:00:00', 'pendiente', 'media', 'jdnwskof', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('admin','administrativo','medico','auditor','paciente') NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `dni` varchar(20) DEFAULT NULL,
  `fecha_alta` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_baja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `email`, `contrasena`, `rol`, `activo`, `dni`, `fecha_alta`, `fecha_baja`) VALUES
(1, 'Administrador', '', 'admin@test.com', '1234', 'admin', 1, '11111111', '2026-06-16 19:16:35', NULL),
(2, 'Maria', 'Perez', 'M.Perez@mediflow.com', '1234', 'medico', 1, '22222222', '2026-06-16 19:16:35', NULL),
(5, 'Mica', 'Le Donne', 'mica@gmail.com', '1234', 'auditor', 1, '46867952', '2026-06-16 19:16:35', NULL),
(6, 'Luis', 'Pasteur', 'LuisP@gmail.com', '1234', 'medico', 1, '21371255', '2026-06-16 19:16:35', NULL),
(7, 'Micaela', 'Le Donne', 'micaledonne@gmail.com', '1234', 'paciente', 1, '46866891', '2026-06-16 19:16:35', NULL),
(8, 'Matias', 'Gomez', 'MatiG@gmail.com', '1234', 'paciente', 1, '46959611', '2026-06-16 19:16:35', NULL),
(9, 'Pedro', 'Martin', 'pedromartin@gmail.com', '1234', 'paciente', 1, '46555790', '2026-06-16 19:16:35', NULL),
(10, 'Julieta', 'Lusch', 'julietalussh@gmail.com', '1234', 'paciente', 1, '47718689', '2026-06-16 19:16:35', NULL),
(16, 'Franco', 'Bazán', 'bazan5717@gmail.com', '$2y$10$CJtmA5lAKE/6M7I5HyOR.O3HzeGspuxbI0qAtZRwQNWJ0u6YQn1Ii', 'paciente', 0, '42793521', '2026-06-16 19:32:09', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivo`
--
ALTER TABLE `archivo`
  ADD PRIMARY KEY (`id_archivo`),
  ADD KEY `id_solicitud` (`id_solicitud`);

--
-- Indices de la tabla `auditor`
--
ALTER TABLE `auditor`
  ADD PRIMARY KEY (`id_auditor`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD PRIMARY KEY (`id_evaluacion`),
  ADD KEY `id_solicitud` (`id_solicitud`),
  ADD KEY `id_auditor` (`id_auditor`);

--
-- Indices de la tabla `medico`
--
ALTER TABLE `medico`
  ADD PRIMARY KEY (`id_medico`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `matricula` (`matricula`);

--
-- Indices de la tabla `paciente`
--
ALTER TABLE `paciente`
  ADD PRIMARY KEY (`id_paciente`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `nro_afiliado` (`nro_afiliado`);

--
-- Indices de la tabla `permiso`
--
ALTER TABLE `permiso`
  ADD PRIMARY KEY (`id_permiso`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`id_plan`);

--
-- Indices de la tabla `practica`
--
ALTER TABLE `practica`
  ADD PRIMARY KEY (`id_practica`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `solicitud`
--
ALTER TABLE `solicitud`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_medico` (`id_medico`),
  ADD KEY `id_practica` (`id_practica`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivo`
--
ALTER TABLE `archivo`
  MODIFY `id_archivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `auditor`
--
ALTER TABLE `auditor`
  MODIFY `id_auditor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  MODIFY `id_evaluacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `medico`
--
ALTER TABLE `medico`
  MODIFY `id_medico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `paciente`
--
ALTER TABLE `paciente`
  MODIFY `id_paciente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `permiso`
--
ALTER TABLE `permiso`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `planes`
--
ALTER TABLE `planes`
  MODIFY `id_plan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `practica`
--
ALTER TABLE `practica`
  MODIFY `id_practica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `solicitud`
--
ALTER TABLE `solicitud`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `archivo`
--
ALTER TABLE `archivo`
  ADD CONSTRAINT `archivo_ibfk_1` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitud` (`id_solicitud`);

--
-- Filtros para la tabla `auditor`
--
ALTER TABLE `auditor`
  ADD CONSTRAINT `auditor_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD CONSTRAINT `evaluacion_ibfk_1` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitud` (`id_solicitud`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluacion_ibfk_2` FOREIGN KEY (`id_auditor`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `medico`
--
ALTER TABLE `medico`
  ADD CONSTRAINT `medico_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
