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
- Parametrización dinámica de planes médicos (PMO inicial, Plenitud200, MediPro) desde la base de datos

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
- **Testing:** PHPUnit 10.5 + Xdebug (Code Coverage)
---

## Cómo ejecutar el proyecto
Para ejecutar el sistema MediFlow en tu entorno local, es necesario contar con un servidor web y un motor de base de datos. Recomendamos utilizar **XAMPP**.

### Requisitos previos
* Servidor local XAMPP con PHP >= 8.1 habilitado.
* Composer instalado en la computadora (requerido para ejecutar la suite de pruebas).

### 1. Preparación del Entorno (Instalación de XAMPP)
1. Descargá XAMPP desde su [página oficial](https://www.apachefriends.org/es/index.html) (recomendamos la versión con PHP 8.x).
2. Instalalo manteniendo las opciones por defecto (asegurate de que **Apache** y **MySQL** estén seleccionados).
3. Abrí el "XAMPP Control Panel".
4. Iniciá los módulos haciendo clic en el botón **Start** al lado de `Apache` y `MySQL`. Ambos deben quedar resaltados en color verde.

### 2. Clonar el repositorio
1. Abrí la consola (Terminal, CMD o PowerShell).
2. Navegá hasta la carpeta `htdocs` dentro de la instalación de XAMPP. Por lo general, en Windows el comando es:
   ```bash
   cd C:\xampp\htdocs

### 3.Ejecuta el comando para descargar el proyecto en tu computadora:
git clone [https://github.com/UCH-LDS-2026/grupo-01.git](https://github.com/UCH-LDS-2026/grupo-01.git)

### 4.Ingresar a la carpeta principal del proyecto
cd grupo_01

### 5.Configurar la BD
sistema requiere una base de datos MySQL estructurada para funcionar.

1. En tu navegador web, ingresá a http://localhost/phpmyadmin/.

2. Hacé clic en la pestaña Bases de datos en la barra superior.

3. En el campo "Nombre de la base de datos", escribí mediflow y hacé clic en Crear.

4. Seleccioná la base de datos mediflow recién creada en el panel izquierdo.

5. Andá a la pestaña Importar (en la barra superior).

6. Hacé clic en Seleccionar archivo (o "Choose file").

7. Buscá dentro de la carpeta de tu proyecto local (C:\xampp\htdocs\grupo-01) el archivo schema.sql (o el archivo de exportación de tu BD) y seleccionalo.

8. Desplazate hacia abajo y hacé clic en Importar o Continuar.

### 6.Configurar Permisos (Subir archivos)
El sistema permite la subida de recetas y estudios médicos.

1. Asegurate de que dentro de la carpeta MediFlow/ exista una carpeta llamada uploads/.

2. Si no existe, creala manualmente de forma que la ruta quede así: C:\xampp\htdocs\grupo-01\MediFlow\uploads.

### 7.Ejecutar la aplicacion
Abrí tu navegador web.

1. Ingresá a la siguiente dirección para iniciar 
2. sesión en el sistema: http://localhost/grupo-01/MediFlow/vista/login.php

### 8.Ejecucion de Tests Unitarios:
incluye pruebas unitarias para validar la lógica de negocio. Para ejecutarlo debemos hacer:

1. Instalación de Dependencias
-Si no tenés Composer, instalalo desde getcomposer.org.

-Asegurate de tener activada la extensión ZIP en XAMPP:

En el panel de XAMPP, andá a Config > PHP (php.ini).

Buscá la línea ;extension=zip y quitale el punto y coma inicial para que quede extension=zip.

Guardá el archivo y reiniciá Apache.

-Abrí la terminal dentro de la carpeta del proyecto (grupo-01/) y ejecutá: composer require --dev phpunit/phpunit

2. Correr las Pruebas
Para ejecutar las pruebas en la consola (Windows PowerShell/CMD), utilizá el siguiente comando:.\vendor\bin\phpunit tests\

3. Generar el Reporte (Entregable)
Para guardar el resultado de los tests en un archivo de texto dentro de la carpeta docs/: .\vendor\bin\phpunit tests\ > docs\reporte_tests.txt

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
