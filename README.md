# Proyecto-Gestion-Permisos
<h2>Contexto:</h2>
Dentro de una empresa, para una postulación sobre el puesto de BackEnd Developer se requirio evaluar las habilidades tecnicas mediante el desarrollo de una aplicación real que simula los desafíos y requerimientos típicos de sus proyectos.

<h3>¿Qué se desarrollo?</h3>

Un “Sistema de Gestión de Permisos de Salida” - una aplicación web completa que permite a los empleados solicitar permisos de salida durante el horario laboral, y a los supervisores gestionar las aprobaciones.

<h3>Stack Tecnologico utilizado</h3>

  - Laravel 11+
  - PHP 8.1+
  - Angular
  - PostgreSQL
  - Docker

<h2>Backend:</h2>

<h3>Arquitectura del sistema</h3>
El backend está desarrollado como una API RESTful utilizando Laravel, siguiendo una arquitectura Cliente-Servidor orientada a servicios.
La aplicación está diseñada como un backend desacoplado, sin vistas, pensado para ser consumido por un frontend SPA.

<h3>Separación por Capas Lógicas</h3>

La estructura del backend utiliza una arquitectura en capas que separa claramente:

  - Requests: validación y autorización de entrada
  - Controllers: orquestación del flujo
  - Models: reglas de negocio y persistencia
  - Resources (DTOs): representación de salida

<h3>Autenticación y Autorización</h3>

La autenticación se implementa mediante Laravel Sanctum, utilizando tokens personales y un enfoque stateless.

El sistema implementa Role-Based Access Control (RBAC) mediante middleware, definiendo roles como: 
    
    Empleado 
    Supervisor 
    RRHH

Además, se aplican reglas de autorización basadas en la pertenencia del recurso.

<h3>Patrones de Diseño Utilizados</h3>

Arquitectura Cliente–Servidor (API REST)
  - La aplicación sigue una arquitectura Cliente–Servidor, donde el backend funciona como un servidor de API RESTful y el frontend (SPA en Angular) actúa como cliente consumidor de la API. El backend no renderiza vistas ni maneja lógica de presentación, sino que expone endpoints que devuelven respuestas JSON, desacoplados completamente del frontend.

RESTful API
  - Diseño de la API basado en recursos, utilizando verbos HTTP semánticos y códigos de estado estándar, para permitir consumo consistente por clientes externos.
    
Active Record (Eloquent ORM)
  - Cada modelo encapsula tanto los datos como las operaciones de persistencia en la base de datos, simplificando la manipulación de entidades.
    
Rich Domain Model
  - Los modelos contienen reglas de negocio y comportamientos asociados a la entidad, evitando que la lógica se disperse en los controllers.
    
Middleware Pattern
  - Separa y centraliza la lógica transversal, como autenticación y autorización, que se aplica antes o después de ejecutar los controllers.

Role-Based Access Control (RBAC)
  - Control de acceso basado en roles de usuario (empleado, supervisor, rrhh), asegurando que solo ciertos roles puedan ejecutar acciones específicas.

Soft Delete Pattern
  - Permite “eliminar” registros de manera lógica sin borrarlos físicamente, facilitando la recuperación de datos y la auditoría.

Token-Based Authentication
  - Autenticación stateless mediante tokens personales, gestionados por Laravel Sanctum, que se envían en cada request para validar la identidad del usuario.

State-driven Workflow
  - Flujo de negocio de los permisos basado en estados (pendiente, aprobado, rechazado, cancelado), con reglas que dependen del estado actual de la entidad.

Consistent JSON Error Handling
  - Todas las respuestas de error siguen un formato JSON uniforme, con mensajes claros y códigos HTTP apropiados, mejorando la interacción con el frontend.

Form Request Pattern
  - La validación y autorización de datos de entrada se gestionan mediante Form Requests, permitiendo encapsular las reglas de validación y permisos en clases dedicadas. Manteniendo  los controllers livianos y favorece una separación clara de responsabilidades.

Data Transfer Object (DTO) / API Resources
  - La aplicación utiliza API Resources como DTOs para definir de forma explícita la estructura de los datos expuestos por la API. Esta capa de transformación desacopla los modelos internos del formato de respuesta, mejora la seguridad y garantiza consistencia en las respuestas JSON.

<h3>Gestión de Permisos</h3>

El módulo de permisos implementa un flujo de negocio basado en estados, con validaciones de horario laboral, disponibilidad de 	horas y control de aprobación por roles autorizados.

<h3>Grafico Representativo</h3>

Grafico representativo de la estructura interna del proyecto
<br></br>

<img width="1051" height="841" alt="BackEnd Diagrama" src="https://github.com/user-attachments/assets/a8ed2324-8c62-4b95-b57c-616686c1db19" />

<br></br>
<h3>Observaciones</h3>

  - Para el registro de usuarios se tuvo en cuenta que solamente el rol de RRHH puede crear nuevos usuarios.
  - Los roles se manejan como strings validados a nivel aplicación para mantener flexibilidad y evitar migraciones innecesarias ante cambios futuros.
  - Usuarios de prueba:
    - empleado@demo.com / password
    - supervisor@demo.com / password
    - rrhh@demo.com / password

<h3>Para levantar el proyecto</h3>

<h5>Requisitos previos</h5>

Antes de levantar el proyecto, asegurate de tener instalado:

  - Docker y Docker Compose
  - PHP 8.1 o superior
  - Composer
  - Git

Una vez clonado el proyecto:
  - En la raiz del proyecto
    - Instalar dependencias de PHP:

          composer install

    - Para levantar los contenedores:

          docker compose up -d
      
    - Ejecutar las migraciones:
  
          php artisan migrate
      
    - Ejecutar los seeders:

          php artisan db:seed

    - Ejecutar el proyecto:
      
          php artisan serve

<h2>FrontEnd:</h2>

A modo de representacion, aqui hay un grafico sobre la estructuracion de carpetas del proyecto de FrontEnd.
<br></br>

<img width="947" height="692" alt="FrontEnd Diagrama" src="https://github.com/user-attachments/assets/f4897094-4a10-424b-a2d8-a95f24e5268f" />

<br></br>
<h3>Estructura del proyecto</h3>
Se estructuro el proyecto de manera que se pueda mantener de manera sencilla.

La estructura del proyecto se basa en:

  app/auth -> Todo lo referido a la autenticacion (login y register de usuarios). 
      Y en un auth.routes para manejar las rutas referidas a la autenticacion.

  app/core -> Contiene los guards, models y services en diferentes subdirectorios.
  - guards -> Encargado de la protección de las rutas.
    
  - models -> Encargado de representar las clases del dominio.
  
  - services -> Encargado de realizar llamadas a los Endpoints del BackEnd.
  
    - services/auth.interceptor.ts -> Es el encargado de sincronizar con Sanctum del BackEnd.

  app/layout -> Contiene elementos que se utilizan en la mayoria de cosas, como el navbar.

  app/pages -> Contiene los distintos componentes que visualizan las vistas (.html .css y .ts)

  app/app.routes.ts -> Es en donde se encuentran todas las rutas, se encuentran protegidas por los guards.

<h3>Levantar el proyecto</h3>
Para poder levantar este proyecto hace falta:

  - Node.js -> https://nodejs.org/es
    
  - npm -> Por lo general viene con node
    
  - Angular CLI -> En una consola colocar:

        npm install -g @angular/cli
      
  - Despues de clonar el proyecto, hay que instalar las dependencias -> En una terminal en la raiz del proyecto:

        npm install
      
  - Levantar el proyecto -> En una terminal en la raiz del proyecto:

        ng serve



