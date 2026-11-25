<?php

namespace App\Services\Backup;

/**
 * Divide un script SQL en sentencias individuales.
 *
 * El separador es el punto y coma, pero se ignoran los que aparecen dentro
 * de cadenas, identificadores entre backticks y comentarios. También se
 * respetan los escapes de MySQL con barra invertida y las comillas dobles
 * repetidas ('' o ""), que antes provocaban que un texto como O'Brien; test
 * partiera la sentencia en dos.
 */
class SqlScriptParser
{
    /**
     * @return array<int, string>
     */
    public function parse(string $sql): array
    {
        $sql = $this->quitarBom($sql);
        $statements = [];
        $current = '';
        $length = strlen($sql);
        $inString = false;
        $quote = '';
        $inLineComment = false;
        $inBlockComment = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($inLineComment) {
                if ($char === "\n" || $char === "\r") {
                    $inLineComment = false;
                    // Un comentario equivale a un espacio, no a una unión.
                    $current .= ' ';
                }

                continue;
            }

            if ($inBlockComment) {
                if ($char === '*' && $next === '/') {
                    $inBlockComment = false;
                    $i++;
                    $current .= ' ';
                }

                continue;
            }

            if ($inString) {
                $current .= $char;

                // En MySQL una barra invertida escapa el siguiente carácter.
                // En identificadores entre backticks no se usa este escape.
                if ($char === '\\' && $quote !== '`') {
                    if ($i + 1 < $length) {
                        $current .= $sql[++$i];
                    }

                    continue;
                }

                if ($char === $quote) {
                    // Comilla escapada por duplicación: '' o "".
                    if ($next === $quote) {
                        $current .= $sql[++$i];

                        continue;
                    }

                    $inString = false;
                }

                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $inString = true;
                $quote = $char;
                $current .= $char;

                continue;
            }

            // En MySQL "--" solo inicia comentario si le sigue un espacio,
            // un salto de línea o el final del archivo.
            if ($char === '-' && $next === '-') {
                $tercero = $i + 2 < $length ? $sql[$i + 2] : '';

                if ($tercero === '' || ctype_space($tercero)) {
                    $inLineComment = true;
                    $i++;

                    continue;
                }
            }

            if ($char === '#') {
                $inLineComment = true;

                continue;
            }

            if ($char === '/' && $next === '*') {
                $inBlockComment = true;
                $current .= ' ';
                $i++;

                continue;
            }

            if ($char === ';') {
                $this->agregar($statements, $current);
                $current = '';

                continue;
            }

            $current .= $char;
        }

        $this->agregar($statements, $current);

        return $statements;
    }

    /**
     * @param array<int, string> $statements
     */
    private function agregar(array &$statements, string $statement): void
    {
        $statement = trim($statement);

        if ($statement !== '') {
            $statements[] = $statement;
        }
    }

    private function quitarBom(string $sql): string
    {
        if (str_starts_with($sql, "\xEF\xBB\xBF")) {
            return substr($sql, 3);
        }

        return $sql;
    }
}
