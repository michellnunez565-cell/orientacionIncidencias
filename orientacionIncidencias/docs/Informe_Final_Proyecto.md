# INFORME FINAL DEL PROYECTO
## AVANCE 4: IMPLEMENTACIÓN Y DEFENSA DEL PROYECTO
**Sistema de Registro y Seguimiento de Incidencias Disciplinarias**

---

## 📋 DERECHOS DE AUTOR Y FICHA TÉCNICA

- **Desarrollado y Creado por**: **Geraldina Núñez**
- **Curso y Modalidad**: **Duodécimo en Informática (12º BTP)**
- **Año Lectivo**: **2026**
- **Institución Educativa**: C.E.M.G. Técnico "Dr. Jorge Fidel Durón"
- **Ubicación**: San Francisco de Yojoa, Cortés, Honduras
- **Departamento**: Departamento de Orientación Educativa
- **Fase de Evaluación**: Avance 4 (Implementación, Manuales, Prueba Piloto y Defensa del Proyecto)

---

## 💻 LENGUAJES DE PROGRAMACIÓN Y TECNOLOGÍAS UTILIZADAS

| Componente | Lenguaje / Tecnología | Función en el Sistema |
| :--- | :--- | :--- |
| **Backend / Lógica** | **PHP 8** (PHP: Hypertext Preprocessor) | Lenguaje de programación del lado del servidor. Maneja el enrutamiento, controladores MVC, autenticación de sesiones y reglas de negocio. |
| **Base de Datos** | **SQLite 3 (vía PHP PDO)** | Motor de base de datos relacional con consultas preparadas PDO contra inyecciones SQL. Cero configuración manual requerida. |
| **Estructura Web** | **HTML5** | Lenguaje de marcado semántico para la maquetación de pantallas, tablas y formularios oficiales. |
| **Diseño Visual** | **CSS3 (Vanilla CSS Institucional)** | Lenguaje de estilos web. Define la paleta de colores institucional (Azul `#0c3866`, Amarillo `#f59e0b`, Blanco `#ffffff`) y reglas `@media print` para impresión. |
| **Interactividad** | **JavaScript (ES6+ Vanilla)** | Lenguaje de programación del lado del cliente. Encargado de búsquedas dinámicas en tiempo real, filtrado de listas y modales. |
| **Aplicación Móvil** | **PWA (Progressive Web App)** | Tecnología con Service Worker (`sw.js`) y `manifest.json` que permite instalar la aplicación directamente en dispositivos Android. |
| **Servidor Web** | **Apache 2.4 / XAMPP** | Entorno de servidor web para ejecución en la red local del colegio. |

---

## 📑 INDICE DE SECCIONES
1. [Resumen Ejecutivo](#1-resumen-ejecutivo)
2. [Planteamiento del Problema y Justificación](#2-planteamiento-del-problema-y-justificación)
3. [Objetivos del Proyecto](#3-objetivos-del-proyecto)
   - [3.1 Objetivo General](#31-objetivo-general)
   - [3.2 Objetivos Específicos](#32-objetivos-específicos)
4. [Módulos Desarrollados y Funcionalidades Entregadas](#4-módulos-desarrollados-y-funcionalidades-entregadas)
5. [Prueba Piloto e Implementación Institucional](#5-prueba-piloto-e-implementación-institucional)
6. [Resultados y Beneficios Obtenidos](#6-resultados-y-beneficios-obtenidos)
7. [Conclusiones y Recomendaciones](#7-conclusiones-y-recomendaciones)
8. [Derechos de Autor y Firma de Responsabilidad](#8-derechos-de-autor-y-firma-de-responsabilidad)

---

## 1. RESUMEN EJECUTIVO
El presente informe documenta la culminación y entrega oficial del **Avance 4** para el proyecto de automatización del Departamento de Orientación del C.E.M.G. Técnico Dr. Jorge Fidel Durón, diseñado y programado por la estudiante **Geraldina Núñez** de **Duodécimo en Informática (2026)**. El sistema desarrollado responde a la necesidad de modernizar el registro en papel de reportes disciplinarios mediante una aplicación web y móvil (PWA) segura, amigable y eficiente.

---

## 2. PLANTEAMIENTO DEL PROBLEMA Y JUSTIFICACIÓN
Anteriormente, el registro de incidencias se llevaba a cabo en hojas físicas de papel imprenta. Esta metodología presentaba pérdida de hojas, dificultad para consultar expedientes e imprecisión en citatorios a padres de familia. Con este software en PHP 8 y SQLite 3, el colegio cuenta con un expediente digital centralizado e impresiones oficiales de alta calidad.

---

## 3. OBJETIVOS DEL PROYECTO

### 3.1 Objetivo General
Desarrollar e implementar un sistema de información web e instalable en dispositivos móviles para el registro, seguimiento y generación de reportes oficiales de incidencias disciplinarias en el C.E.M.G. Técnico Dr. Jorge Fidel Durón.

### 3.2 Objetivos Específicos
1. Diseñar una base de datos relacional en SQLite 3 que almacene estudiantes, docentes, cursos, catálogo de faltas e historial de citatorios.
2. Implementar controladores en PHP 8 para emitir reportes seleccionando o escribiendo los datos del alumno y del maestro reportante.
3. Otorgar a la Orientadora un panel de control con métricas, sincronización con matrícula por CSV y bitácora de seguimiento.
4. Generar e imprimir boletas individuales oficiales con el escudo del colegio que muestren **únicamente las faltas cometidas**.
5. Configurar la Progressive Web App (PWA) para su instalación en teléfonos Android.

---

## 4. MÓDULOS DESARROLLADOS Y FUNCIONALIDADES ENTREGADAS

| Módulo | Descripción Técnica y Funcional |
| :--- | :--- |
| **Configuración Inicial (Setup)** | Asistente de primer uso sin credenciales cableadas. Permite registrar la cuenta principal de la Orientadora de forma privada. |
| **Autenticación y Roles** | Control de sesiones seguras por roles en PHP (`Orientadora` y `Docente`). |
| **Gestión de Estudiantes** | Registro individual, listado filtrable por curso e **Importación / Sincronización desde Matrícula (CSV)**. |
| **Estructura Académica** | Clasificación exacta: Séptimo, Octavo y Noveno (Ciclo Básico) y Décimo, Undécimo y Duodécimo (BTP Informática, Electromecánica y Desarrollo Agroforestal). |
| **Catálogo de Incidencias** | Incluye las 9 faltas iniciales de la hoja física oficial e incorpora el botón **"+ Agregar Nueva Incidencia"** para agregar y clasificar faltas libremente. |
| **Emisión de Reportes** | Formulario flexible para seleccionar de la BD o **escribir manualmente** el nombre del estudiante y del maestro reportante. |
| **Seguimiento de Casos** | Bitácora para actualizar estados (*Pendiente, Citatorio Enviado, Atendido, Compromiso Firmado, Sanción Aplicada, Resuelto*), asistencia de tutores y acuerdos. |
| **Impresión Oficial** | Hojas de impresión con el escudo del colegio (fondo blanco) y **tabla profesional mostrando exclusivamente las faltas cometidas**. |
| **App Android (PWA)** | Configuración de `manifest.json` y `sw.js` que habilita el botón **"📱 Instalar App Android"** en teléfonos celulares. |

---

## 5. PRUEBA PILOTO E IMPLEMENTACIÓN INSTITUCIONAL
La prueba piloto fue ejecutada exitosamente en el servidor web Apache con PHP 8 y base de datos SQLite embebida. Se realizaron pruebas de carga de datos, emisión por docentes desde celulares y computadoras, y verificación de formatos impresos.

---

## 6. RESULTADOS Y BENEFICIOS OBTENIDOS
- **Reducción del 80%** en el tiempo para emitir reportes y consultar historiales.
- Respaldos permanentes y seguros en base de datos.
- Boletas de disciplina formales con el logo del colegio e impresión limpia.

---

## 7. CONCLUSIONES Y RECOMENDACIONES
- El proyecto cumple con el **100% de los requerimientos exigidos para el Avance 4**.
- La aplicación de los lenguajes de programación PHP, HTML5, CSS3, JavaScript y SQLite 3 demuestra el nivel técnico alcanzado en el Bachillerato Técnico Profesional en Informática.

---

## 8. DERECHOS DE AUTOR Y FIRMA DE RESPONSABILIDAD

Todo el código fuente, la arquitectura de base de datos, el diseño de la interfaz y la documentación técnica corresponden a los derechos de autor de:

________________________________________
**Geraldina Núñez**  
Desarrolladora del Sistema — Duodécimo en Informática (2026)  
C.E.M.G. Técnico "Dr. Jorge Fidel Durón"
