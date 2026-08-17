# MANUAL TÉCNICO
## Sistema de Registro de Incidencias Disciplinarias
**Centro de Educación Media Gubernamental Técnico "Dr. Jorge Fidel Durón"**
*San Francisco de Yojoa, Cortés — Departamento de Orientación*

---

## 🛠️ TABLA DE CONTENIDOS
1. [Especificaciones Técnicas y Requerimientos del Sistema](#1-especificaciones-técnicas-y-requerimientos-del-sistema)
2. [Arquitectura del Software](#2-arquitectura-del-software)
3. [Estructura de Directorios](#3-estructura-de-directorios)
4. [Modelo Entidad-Relación y Esquema de Base de Datos](#4-modelo-entidad-relación-y-esquema-de-base-de-datos)
5. [Descripción de Componentes y Controladores](#5-descripción-de-componentes-y-controladores)
6. [Módulo PWA (Progressive Web App) y Service Worker](#6-módulo-pwa-progressive-web-app-y-service-worker)
7. [Instalación, Despliegue y Mantenimiento](#7-instalación-despliegue-y-mantenimiento)

---

## 1. ESPECIFICACIONES TÉCNICAS Y REQUERIMIENTOS DEL SISTEMA

### Requerimientos de Servidor / Host:
- **Servidor Web**: Apache 2.4+ (vía XAMPP, WAMP, Nginx o PHP Built-in server).
- **Lenguaje de Programación**: PHP 8.0 o superior (con extensiones `pdo`, `pdo_sqlite` habilitadas).
- **Motor de Base de Datos**: SQLite 3 (almacenamiento embebido en archivo `database.sqlite` de cero configuración).

### Requerimientos del Cliente (Navegador):
- Google Chrome 90+, Microsoft Edge 90+, Mozilla Firefox 88+ o Safari 14+.
- Soporte para HTML5, CSS3, JavaScript ES6+ y Service Workers (PWA).

---

## 2. ARQUITECTURA DEL SOFTWARE
El sistema implementa un patrón de diseño **Modelo-Vista-Controlador (MVC)** liviano y modular:
- **Vistas (Views)**: Plantillas HTML5 organizadas con estilos CSS3 institucionales (Azul `#0c3866`, Amarillo `#f59e0b`, Blanco `#ffffff`) y reglas `@media print` para impresiones.
- **Controladores (Controllers)**: Clases PHP orientadas a objetos encargadas de procesar la lógica de negocio, validaciones y autenticación.
- **Modelo de Datos (Data Layer)**: Conexión mediante `PDO` a la base de datos SQLite con sembrado automático de tablas e índices (`PRAGMA foreign_keys = ON;`).

---

## 3. ESTRUCTURA DE DIRECTORIOS

```
c:/xampp/htdocs/orientacionIncidencias/
├── assets/
│   ├── css/
│   │   ├── style.css        # Sistema de diseño UI (Azul, Amarillo y Blanco)
│   │   └── print.css        # Formato de impresión oficial
│   ├── img/
│   │   ├── logo.jpg         # Escudo oficial con fondo blanco (para reportes e interfaz)
│   │   └── logo.png         # Logo en alta resolución con fondo transparente
│   └── js/
│       └── app.js           # Búsqueda en tiempo real, modales y manejador de PWA
├── config/
│   └── database.php        # Conexión PDO SQLite e inicialización de tablas
├── controllers/
│   ├── AuthController.php   # Autenticación, sesiones y asistente de setup
│   ├── StudentController.php# CRUD de estudiantes e importador de Matrícula CSV
│   ├── IncidentController.php# Manejo de incidencias y catálogo dinámico
│   ├── FollowupController.php# Bitácora de seguimiento de casos
│   ├── ReportController.php # Generador de reportes e informes
│   └── UserController.php # Gestión de cuentas de maestros
├── docs/
│   ├── Manual_de_Usuario.md # Guía para el usuario final
│   ├── Manual_Tecnico.md    # Especificaciones de desarrollo y arquitectura
│   └── Informe_Final_Proyecto.md # Informe académico y técnico para defensa
├── views/
│   ├── setup.php            # Pantalla de primer uso
│   ├── login.php            # Autenticación
│   ├── dashboard_orientadora.php # Panel principal
│   ├── dashboard_docente.php    # Panel docente
│   ├── estudiantes.php      # Lista e importador de estudiantes
│   ├── crear_incidencia.php # Formulario de reporte disciplinario
│   ├── catalogo_faltas.php  # Catálogo con botón de agregar faltas
│   ├── seguimiento.php      # Bitácora de atención
│   ├── reporte_estudiante.php# Boleta oficial impresa
│   ├── reporte_seccion.php   # Reporte consolidado impreso
│   ├── usuarios.php         # Administración de usuarios
│   ├── header.php           # Encabezado HTML y menú por roles
│   └── footer.php           # Cierre HTML
├── database.sqlite          # Archivo de base de datos SQLite
├── manifest.json            # Manifest para PWA Android
├── sw.js                    # Service worker para caché offline
└── index.php                # Enrutador principal
```

---

## 4. MODELO ENTIDAD-RELACIÓN Y ESQUEMA DE BASE DE DATOS

```sql
-- 1. Tabla de Usuarios (Orientadoras y Docentes)
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    full_name TEXT NOT NULL,
    role TEXT CHECK(role IN ('orientadora', 'docente')) NOT NULL,
    email TEXT,
    phone TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabla de Cursos / Modalidades y Secciones
CREATE TABLE courses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    grade TEXT NOT NULL,          -- e.g. 7mo, 8vo, 9no, 10mo, 11mo, 12mo
    modality TEXT NOT NULL,       -- e.g. Ciclo Básico, BTP en Informática, BTP en Electromecánica, BTP en Desarrollo Agroforestal
    section_name TEXT NOT NULL,   -- e.g. Sección 1, Sección 2, Sección 3
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(grade, modality, section_name)
);

-- 3. Tabla de Estudiantes
CREATE TABLE students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_code TEXT UNIQUE,
    identity_num TEXT UNIQUE,
    full_name TEXT NOT NULL,
    gender TEXT CHECK(gender IN ('M', 'F')),
    birth_date DATE,
    course_id INTEGER REFERENCES courses(id) ON DELETE SET NULL,
    guardian_name TEXT,
    guardian_phone TEXT,
    guardian_address TEXT,
    documents_submitted TEXT,
    status TEXT DEFAULT 'activo',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tabla de Catálogo de Tipos de Incidencias / Faltas
CREATE TABLE incident_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT UNIQUE,
    name TEXT NOT NULL,
    category TEXT DEFAULT 'General',
    severity TEXT CHECK(severity IN ('Leve', 'Grave', 'Muy Grave')) DEFAULT 'Leve',
    description TEXT,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabla de Reportes Disciplinarios
CREATE TABLE incident_reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_code TEXT UNIQUE NOT NULL,
    student_id INTEGER NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    reporter_user_id INTEGER NOT NULL REFERENCES users(id),
    custom_reporter_name TEXT,
    report_date DATE DEFAULT (DATE('now', 'localtime')),
    report_time TIME DEFAULT (TIME('now', 'localtime')),
    observations TEXT,
    severity_level TEXT CHECK(severity_level IN ('Leve', 'Grave', 'Muy Grave')) DEFAULT 'Leve',
    status TEXT CHECK(status IN ('Pendiente', 'Citatorio Enviado', 'Atendido en Orientación', 'Compromiso Firmado', 'Sanción Aplicada', 'Resuelto')) DEFAULT 'Pendiente',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 6. Tabla Pivote de Faltas cometidas por Reporte
CREATE TABLE incident_report_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER NOT NULL REFERENCES incident_reports(id) ON DELETE CASCADE,
    incident_type_id INTEGER NOT NULL REFERENCES incident_types(id) ON DELETE CASCADE
);

-- 7. Tabla de Bitácora de Seguimiento de Casos
CREATE TABLE case_followups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER NOT NULL REFERENCES incident_reports(id) ON DELETE CASCADE,
    followup_date DATE DEFAULT (DATE('now', 'localtime')),
    user_id INTEGER NOT NULL REFERENCES users(id),
    action_taken TEXT NOT NULL,
    guardian_present INTEGER DEFAULT 0,
    agreement_notes TEXT,
    next_date DATE,
    status_assigned TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5. DESCRIPCIÓN DE COMPONENTES Y CONTROLADORES

- **`index.php`**: Enrutador centralizado de peticiones HTTP. Verifica autenticación y despacha a la vista o controlador correspondiente.
- **`AuthController`**: Administra el inicio de sesión, hash seguro de contraseñas (`password_hash`), cierre de sesión y la validación de primer uso (`hasUsers()`).
- **`StudentController`**: Maneja el registro de alumnos, consultas filtradas e importación masiva mediante lectura de streams de archivos CSV (`importMatriculaCSV`).
- **`IncidentController`**: Controla la inserción de incidencias, adición dinámica de faltas al catálogo (`addIncidentType`) y generación de reportes multi-item.
- **`FollowupController`**: Registra notas de entrevistas, citatorios a tutores, cambios de estados y acuerdos alcanzados.
- **`ReportController`**: Agrupa y procesa la información para renderizar la boleta individual oficial y los resúmenes consolidados por sección.

---

## 6. MÓDULO PWA (PROGRESSIVE WEB APP) Y SERVICE WORKER
- **`manifest.json`**: Define el nombre de la aplicación, colores del tema (`#0c3866`), orientación e íconos en resolución `192x192` y `512x512`.
- **`sw.js`**: Service worker con estrategia Network-First que almacena en caché los recursos estáticos (`style.css`, `print.css`, `app.js`, `logo.jpg`).
- **PWA Prompt**: Captura el evento `beforeinstallprompt` en `app.js` para mostrar el botón **"📱 Instalar App Android"** en dispositivos móviles.

---

## 7. INSTALACIÓN, DESPLIEGUE Y MANTENIMIENTO
1. **Despliegue**: Copiar el directorio `orientacionIncidencias` a la carpeta `htdocs` de XAMPP.
2. **Copia de Seguridad**: Basta con realizar un respaldo periódico del archivo `database.sqlite`.
3. **Restauración**: Reemplazar el archivo `database.sqlite` en caso de requerir restauración de datos.
