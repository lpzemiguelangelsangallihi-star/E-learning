<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('IOP utiliza MySQL de pruebas', function () {

    expect(config('database.default'))
        ->toBe('mysql');

    expect(config('database.connections.mysql.database'))
        ->toBe('e_learning_test');

    $resultado = DB::select(
        'SELECT DATABASE() AS base_actual'
    );

    expect($resultado[0]->base_actual)
        ->toBe('e_learning_test');
});


test('las tablas principales de IOP existen', function () {

    $tablas = [
        'iop_modelos_decision',
        'iop_recomendaciones',
        'iop_seguimientos',
        'iop_transiciones',
        'iop_matrices_markov',
        'iop_ejercicios_refuerzo',
        'iop_asignaciones_refuerzo',
    ];

    foreach ($tablas as $tabla) {
        expect(
            Schema::hasTable($tabla)
        )->toBeTrue(
            "No existe la tabla: {$tabla}"
        );
    }
});

test('la asignacion contiene campos para verificar ejercicios', function () {

    $columnas = [
        'respuesta_estudiante',
        'entregado_en',
        'retroalimentacion',
        'revisado_por',
        'revisado_en',
        'completado_en',
    ];

    foreach ($columnas as $columna) {
        expect(
            Schema::hasColumn(
                'iop_asignaciones_refuerzo',
                $columna
            )
        )->toBeTrue(
            "Falta la columna: {$columna}"
        );
    }

});

test('existe la tabla de ciclos de refuerzo IOP', function () {
    expect(
        Schema::hasTable('iop_ciclos_refuerzo')
    )->toBeTrue();
});

test('los ciclos contienen relaciones esenciales', function () {
    $columnas = [
        'seguimiento_id',
        'ciclo',
        'recomendacion_id',
        'transicion_origen_id',
        'created_at',
        'updated_at',
    ];

    foreach ($columnas as $columna) {
        expect(
            Schema::hasColumn(
                'iop_ciclos_refuerzo',
                $columna
            )
        )->toBeTrue(
            "Falta el campo: {$columna}"
        );
    }
});

test('el seguimiento registra su consolidacion', function () {
    $columnas = [
        'estudiante_id',
        'leccion_id',
        'estado_actual',
        'ciclo_actual',
        'activo',
        'consolidado_en',
        'intento_diagnostico_id',
    ];

    foreach ($columnas as $columna) {
        expect(
            Schema::hasColumn(
                'iop_seguimientos',
                $columna
            )
        )->toBeTrue(
            "Falta el campo: {$columna}"
        );
    }
});
