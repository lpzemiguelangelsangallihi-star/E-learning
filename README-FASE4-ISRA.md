# Fase 4 – Estadísticas y gráficos complementarios

Esta versión integra los cambios de estadísticas sobre la versión actualizada del proyecto.
Se conservaron las migraciones, modelos, controladores, vistas y demás archivos nuevos de la versión actualizada.

## Áreas incluidas
- Dashboard administrativo y estadísticas generales.
- Dashboard docente y estadísticas de sus cursos.
- Filtros de fechas y curso en reportes estadísticos.
- Indicadores de progreso y resultados de evaluaciones por curso.

## Prueba
1. Instalar dependencias con `composer install` y `npm install`.
2. Revisar la configuración de `.env` y la conexión de base de datos.
3. Ejecutar las migraciones pendientes con `php artisan migrate`.
4. Iniciar `php artisan serve` y `npm run dev` en terminales separadas.
5. Probar las vistas con un usuario administrativo y uno docente.

Nota: esta integración no sustituye una prueba funcional en el entorno local del proyecto.
