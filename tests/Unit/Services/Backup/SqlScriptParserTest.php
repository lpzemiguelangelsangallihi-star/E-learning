<?php

use App\Services\Backup\SqlScriptParser;

it('separa sentencias y respeta el punto y coma dentro de cadenas escapadas', function () {
    $sql = <<<'SQL'
CREATE TABLE `prueba` (`id` int, `texto` varchar(255));
INSERT INTO `prueba` VALUES (1, 'O\'Brien; test');
SQL;

    $sentencias = (new SqlScriptParser)->parse($sql);

    expect($sentencias)->toHaveCount(2)
        ->and($sentencias[0])->toStartWith('CREATE TABLE')
        ->and($sentencias[1])->toContain("O\\'Brien; test");
});

it('respeta comillas dobles repetidas y omite comentarios', function () {
    $sql = <<<'SQL'
-- comentario; con punto y coma
CREATE TABLE `prueba` (`id` int, `texto` varchar(255));
/* otro; comentario */
INSERT INTO `prueba` VALUES (2, 'comillas ''dobles''; y más');
# comentario de almohadilla;
INSERT INTO `prueba` VALUES (3, 'final');
SQL;

    $sentencias = (new SqlScriptParser)->parse($sql);

    expect($sentencias)->toHaveCount(3)
        ->and($sentencias[1])->toContain("''dobles'';")
        ->and(implode(' ', $sentencias))->not->toContain('comentario');
});

it('respeta identificadores entre backticks que contienen punto y coma', function () {
    $sql = "CREATE TABLE `tabla;rara` (id INT);\nSELECT `a;b` FROM `tabla;rara`;";

    $sentencias = (new SqlScriptParser)->parse($sql);

    expect($sentencias)->toHaveCount(2)
        ->and($sentencias[0])->toContain('`tabla;rara`')
        ->and($sentencias[1])->toContain('`a;b`');
});

it('conserva las barras invertidas escapadas dentro de las cadenas', function () {
    $sql = <<<'SQL'
CREATE TABLE `rutas` (`ruta` varchar(255));
INSERT INTO `rutas` VALUES ('C:\\carpeta\\archivo.txt');
SQL;

    $sentencias = (new SqlScriptParser)->parse($sql);

    expect($sentencias)->toHaveCount(2)
        ->and($sentencias[1])->toContain('C:\\\\carpeta\\\\archivo.txt');
});

it('quita el BOM y las sentencias vacías', function () {
    $sql = "\xEF\xBB\xBF;;;\nCREATE TABLE `t` (id int);;;\n";

    $sentencias = (new SqlScriptParser)->parse($sql);

    expect($sentencias)->toHaveCount(1)
        ->and($sentencias[0])->toStartWith('CREATE TABLE');
});

it('no trata el doble guion sin espacio como comentario', function () {
    $sentencias = (new SqlScriptParser)->parse('SELECT 5--3;');

    expect($sentencias)->toHaveCount(1)
        ->and($sentencias[0])->toBe('SELECT 5--3');
});

it('omite los comentarios de versión de MySQL', function () {
    $sql = "/*!40101 SET NAMES utf8mb4 */;\nCREATE TABLE `t` (id int);";

    $sentencias = (new SqlScriptParser)->parse($sql);

    expect($sentencias)->toHaveCount(1)
        ->and($sentencias[0])->toStartWith('CREATE TABLE');
});
