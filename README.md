# ÁTICA 
[![Build Status](https://travis-ci.org/iesoretania/atica-fct.svg?branch=master)](https://travis-ci.org/iesoretania/atica-ng)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/iesoretania/atica-ng/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/iesoretania/atica-ng/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7257a728-0aa7-42f7-978b-e46c8414f492/mini.png)](https://insight.sensiolabs.com/projects/7257a728-0aa7-42f7-978b-e46c8414f492)

Evolución de la aplicación web para la gestión interna de centros educativos (en desarrollo, aún no está lista para producción).

Aunque su origen se encuentra en la gestión documental, la nueva iteración permite la integración de distintos
módulos. En concreto, están proyectados módulos para la gestión de la Formación en Centros de Trabajo (FCT), de
cumplimentación de encuestas, de gestión de entradas/salidas al centro (HORUS), etc.

Este proyecto utiliza [Symfony] y otros muchos componentes que se instalan usando [Composer] y [npmjs].

Para facilitar el desarrollo se proporciona un entorno [Vagrant] con todas las dependencias ya instaladas.

## Requisitos

- PHP 5.5.9 o superior
- Servidor web Apache2 (podría funcionar con nginx, pero no se ha probado aún)
- Servidor de base de datos MySQL 5 o derivado (como MariaDB, Percona, etc). En un futuro podría permitirse también PosgreSQL si hay demanda.
- PHP [Composer]
- [Node.js] y [npmjs] (si se ha descargado una build completa, no serán necesarios)

## Instalación

- Ejecutar `composer install` desde la carpeta del proyecto.
- Ejecutar `npm install -g gulp` (usar `sudo` si fuera necesario)
- Ejecutar `npm install`.
- Ejecutar `gulp`. [Gulp.js] se instala automáticamente con los comandos anteriores.
- Configurar el sitio de Apache2 para que el `DocumentRoot` sea la carpeta `web/` dentro de la carpeta de instalación.
- Modificar el fichero `parameters.yml` con los datos de acceso al sistema gestor de bases de datos deseados y otros parámetros de configuración globales que considere interesantes.
- Para crear la base de datos: `bin/console doctrine:database:create`.
- Para crear las tablas: `bin/console doctrine:schema:create`.
- Para insertar los datos iniciales: `bin/console doctrine:fixtures:load -n` (¡cuidado! Esto elimina todos los datos existentes en la base de datos).

## Entorno de desarrollo

Para poder ejecutar la aplicación en un entorno de desarrollo basta con tener [Vagrant] instalado junto con [VirtualBox]
y ejecutar el comando `vagrant up`. La aplicación será accesible desde la dirección http://192.168.33.10/

## Licencia
Esta aplicación se ofrece bajo licencia [AGPL versión 3].

[Vagrant]: https://www.vagrantup.com/
[VirtualBox]: https://www.virtualbox.org
[Symfony]: http://symfony.com/
[Composer]: http://getcomposer.org
[AGPL versión 3]: http://www.gnu.org/licenses/agpl.html
[Node.js]: https://nodejs.org/en/
[npmjs]: https://www.npmjs.com/
[Gulp.js]: http://gulpjs.com/
