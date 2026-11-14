# Reglas académicas de Investigación Operativa II

## 1. Objetivo
Utilizar el criterio Maximin para recomendar ejercicios
de refuerzo y cadenas de Markov para representar la
evolución académica del estudiante por lección.

## 2. Alternativas Maximin
- Teórico
- Práctico
- Visual

Para cada alternativa se calcula su utilidad mínima.
La recomendación corresponde a la alternativa con
el mayor de esos mínimos.

## 3. Matriz de referencia
- Teórico: [8, 7, 5]
- Práctico: [7, 9, 8]
- Visual: [9, 8, 6]

Los mínimos son 5, 7 y 6.
Sin adaptación, Maximin selecciona práctico.

## 4. Adaptación experimental
Versión: 1.0.0

La política utiliza la calificación normalizada
del estudiante y aplica las siguientes bonificaciones:

- Nota de 0 a 50: teórico +4.
- Nota mayor que 50 y menor que 70: práctico +2.
- Nota de 70 a 100: visual +2.

Las bonificaciones se aplican a las tres utilidades
de la alternativa seleccionada para ajuste.

Es una heurística experimental, no una política
validada empíricamente.

La matriz efectiva de cada recomendación se
conserva en el registro correspondiente.

## 5. Estados académicos de Markov
- S1: calificación de 0 a 50.
- S2: calificación mayor que 50 y menor que 70.
- S3: calificación de 70 a 100.
- S4: aprendizaje consolidado.

Las calificaciones se expresan en escala 0-100.

## 6. Consolidación S4
Para alcanzar S4 se requieren:
- Dos evaluaciones reales consecutivas.
- Calificación mínima de 70 en cada una.
- Refuerzos de los ciclos correspondientes aprobados
  previamente por el profesor.
- Orden cronológico válido de los eventos.

S4 es absorbente para el seguimiento de esa lección.

## 7. Matrices de Markov
Las matrices pueden tener origen:
- Simulación.
- Evaluación real.

Las matrices reales se estiman a partir de las
transiciones académicas registradas.

Una matriz incompleta no se utiliza para proyecciones.
La matriz ilustrativa no se presenta como predicción real.

## 8. Trazabilidad
Cada ciclo mantiene su recomendación, ejercicio y
transición de origen cuando corresponde.

Las evaluaciones repetidas no deben crear duplicados.
Las decisiones históricas no deben sobrescribirse.

## 9. Limitaciones
- La adaptación Maximin todavía necesita calibración
  con evidencia pedagógica.
- Las probabilidades de Markov dependen de la cantidad
  y calidad de las observaciones reales.
- La versión de política está documentada en código,
  pero aún no se persiste individualmente por decisión.
- Las reglas deben validarse con el docente responsable.

## 10. Validación pendiente
La fase final verificará algoritmos, flujos académicos,
permisos, base de datos, casos límite y regresiones.
```
