#### Universidad Champagnat - Laboratorio de Desarrollo de Software - 2026

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

1.Clonar el repositorio:
```bash

## Arquitectura MVC

El sistema de gestión de solicitudes médicas fue desarrollado utilizando la arquitectura MVC (Modelo - Vista - Controlador), permitiendo una mejor organización del código, mantenimiento del sistema y separación de responsabilidades.

### Modelo (Model)
La capa Modelo se encarga de la gestión de datos y de la interacción con la base de datos MySQL. Aquí se encuentran las consultas SQL y la lógica relacionada con:
- Registro y autenticación de usuarios
- Gestión de pacientes
- Creación y evaluación de solicitudes médicas
- Priorización automática de solicitudes
- Historial de solicitudes
- Sistema de alertas

Esta capa permite manipular la información de manera segura y estructurada.

### Vista (View)
La Vista representa la interfaz gráfica del sistema, desarrollada utilizando HTML, CSS, Bootstrap y JavaScript. Su función es mostrar la información al usuario de manera clara e intuitiva.

Entre las principales vistas del sistema se encuentran:
- Pantalla de login
- Dashboard principal
- Formularios de registro
- Panel de gestión de solicitudes
- Historial y consulta de estados

La interfaz fue diseñada para facilitar la navegación y mejorar la experiencia del usuario.

### Controlador (Controller)
La capa Controlador actúa como intermediario entre las vistas y los modelos. Se encarga de:
- Procesar solicitudes del usuario
- Validar formularios y datos ingresados
- Coordinar operaciones entre la interfaz y la base de datos
- Gestionar sesiones y autenticación
- Controlar el flujo de navegación del sistema

Gracias a esta separación, el sistema mantiene una estructura modular y escalable.

### Tecnologías utilizadas
- PHP
- MySQL
- HTML5
- CSS3
- Bootstrap
- JavaScript
- GitHub para control de versiones

--

git clone <http://localhost/MediFlow/grupo-01/MediFlow/vista/login.php>
