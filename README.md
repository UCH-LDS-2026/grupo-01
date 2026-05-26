<img width="1200" height="1125" alt="image" src="https://github.com/user-attachments/assets/96dd130e-b657-4d72-9131-c74cfef375d6" />#### Universidad Champagnat - Laboratorio de Desarrollo de Software - 2026

# Proyecto Final - MediFlow
   
## Grupo N° 1
- Bazan Franco  
- Le Donne Micaela  
- Molina Agostina  
- Müller Lisandro  

---

## Problema que resuelve
La solución propuesta permite digitalizar y agilizar el proceso de autorizaciones médicas en clínicas y obras sociales.  
El sistema optimiza las evaluaciones, prioriza casos urgentes y brinda seguimiento en tiempo real, mejorando la eficiencia operativa y reduciendo los tiempos de espera para los pacientes.

---

## Usuarios
- Médico  
- Paciente  
- Administrativo  
- Administrador del sistema  

---

## Funcionalidades principales
- Registro de solicitudes médicas  
- Evaluación de solicitudes (aprobación/rechazo)  
- Consulta de estado en tiempo real  
- Priorización automática (alta, media, baja)  
- Panel de gestión (dashboard con solicitudes pendientes y urgentes)  
- Adjuntar archivos médicos (órdenes, estudios, etc.)
----
## Arquitectura
El sistema está desarrollado bajo el patrón MVC:
- Modelo: acceso a datos
- Vista: interfaces de usuario
- Controlador: lógica del sistema
---
## Estrategia de ramas

- main → versión estable (protegida)
- develop → integración
- feature/* → nuevas funcionalidades

---

## Stack tecnológico
- **Frontend:** HTML, CSS, Bootstrap  
- **Backend:** PHP  
- **Base de datos:** MySQL  
- **Control de versiones:** Git + GitHub  

---

## Cómo ejecutar el proyecto

2. Importar la base de datos (schema.sql) en phpMyAdmin
3. Configurar conexión en config.php
4. Ejecutar en XAMPP (http://localhost/mediflow/grupo-01/MediFlow/vista/login.php)

---

## 🗄️ Estructura de la Base de Datos

Para replicar este proyecto de forma local, podés usar el siguiente esquema de base de datos. 

<details>
<summary><b></b></summary>

```sql
-- Estructura de la base de datos de MediFlow

CREATE DATABASE IF NOT EXISTS mediflow;
USE mediflow;

-- Tabla de Usuarios
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL
);

-- Tabla de Pacientes
CREATE TABLE IF NOT EXISTS paciente (
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(15) UNIQUE NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20)
);

-- Tabla de medico
CREATE TABLE IF NOT EXISTS medico (
    id_medico INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario (INT11),
    matricula VARCHAR(50) NOT NULL,
    especialidad VARCHAR(100) NOT NULL
);

--Tabla de permiso
CREATE TABLE IF NOT EXISTS permiso (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    descripcion text NOT NULL
);
  
--Tabla de practica
CREATE TABLE IF NOT EXISTS practica (
    id_practica (INT 11) PRIMARY KEY,
    nombre VARCHAR(150),
    descripcion text NOT NULL
);

  --Tabla de rol
CREATE TABLE IF NOT EXISTS rol (
    id_rol (INT 11) PRIMARY KEY,
    nombre VARCHAR(50)
);

  --Tabla de rol_permiso
CREATE TABLE IF NOT EXISTS rol_permiso (
    id_rol (INT 11) PRIMARY KEY,
    id_permiso (INT 11)
);

  --Tabla de solicitud
CREATE TABLE IF NOT EXISTS solicitud (
    id_solicitud (INT 11) PRIMARY KEY,
    id_paciente (INT 11),
    id_medico (INT 11),
    id_practica (INT 11),
    fecha NOT NULL,
    estado NOT NULL
);

 --Tabla de usuario_rol
CREATE TABLE IF NOT EXISTS usuario_rol (
    id_usuario (INT 11) PRIMARY KEY,
    id_rol (INT 11)
);

 --Tabla de evaluacion
CREATE TABLE IF NOT EXISTS evaluacion (
    id_evaluacion (INT 11) PRIMARY KEY,
    id_solicitud (INT 11),
    id_auditor (INT 11),
    estado_nuevo VARCHAR (50),
    observaciones text
);

 --Tabla de auditor
CREATE TABLE IF NOT EXISTS auditor (
    id_auditor (INT 11) PRIMARY KEY,
    id_usuario (INT 11),
    sector VARCHAR (100)
);

 --Tabla de archivo
CREATE TABLE IF NOT EXISTS archivo (
    id_archivo (INT 11) PRIMARY KEY,
    id_solicitud (INT 11),
    nombre VARCHAR (25),
    tipo VARCHAR (50),
    ruta VARCHAR (255),
    fecha_subida detetime
);


