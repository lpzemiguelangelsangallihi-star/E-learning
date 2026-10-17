# Sprint 2 – Gestión académica

## 1. Copiar archivos
Copia el contenido de este zip sobre la raíz de tu proyecto (respeta las carpetas).
Haz `git commit` antes, para poder ver con `git diff` qué se sobrescribió.

Archivos que **reemplazan** a los existentes:
- app/Models/Curso.php, Modulo.php, Leccion.php, Material.php, Video.php
- resources/views/professor/courses/index.blade.php (solo se agregó el enlace "Contenido" y el código de clase)
- app/Http/Controllers/Student/CourseController.php (estaba vacío)

Si ya tenías controladores/vistas/rutas propias de módulos, lecciones o materiales
(la pantalla "Materiales de la lección"), quedan reemplazadas por estas: borra las
rutas viejas para que no choquen con las nuevas.

## 2. routes/web.php (2 cambios)
1. Al final del archivo, **elimina** el bloque de ESTUDIANTE:
   ```php
   Route::middleware([... 'role:estudiante'])->prefix('estudiante')->name('student.')->group(function () {
       Route::get('/dashboard', function () { return view('student.dashboard'); })->name('dashboard');
   });
   ```
2. Agrega al final:
   ```php
   require __DIR__.'/sprint2.php';
   ```

## 3. Comandos
```bash
php artisan migrate          # agrega cursos.codigo_clase y genera código a los cursos existentes
php artisan storage:link     # necesario para ver archivos y videos subidos
php artisan optimize:clear
npm run build                # o npm run dev
```

## 4. Subida de videos grandes
Los videos subidos aceptan hasta 100 MB, pero PHP limita por defecto (2–8 MB).
En php.ini sube `upload_max_filesize = 100M` y `post_max_size = 110M` y reinicia el servidor.
Con enlaces de YouTube/Vimeo no hay límite.

## 5. Probar
1. Profesor → Mis cursos → **Contenido**: crea unidades, temas, reordénalos con las flechas.
2. Publica el curso (Mis cursos → Publicar) y copia el **código de clase**.
3. Tema → **Videos y materiales** → "Un video" (enlace de YouTube o archivo).
4. Publica el tema (icono del ojo).
5. Estudiante (estudiante@elearning.test) → escribe el código → entra al curso → ve la estructura y el tema.
