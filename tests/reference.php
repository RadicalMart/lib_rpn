<?php

class FormulaParser
{
    private const FUNCTIONS = ['sin', 'cos', 'sqrt', 'exp', 'concat', 'myfunc'];
    private const STRING_QUOTE = '"';

    // Токенизация с поддержкой переменных
    public function tokenize(string $formula): array
    {
        $tokens = [];
        $length = strlen($formula);
        $pos = 0;
        $currentToken = '';
        $state = 'start';
        $escapeNext = false;

        while ($pos < $length) {
            $char = $formula[$pos];

            if ($escapeNext) {
                $currentToken .= $char;
                $escapeNext = false;
                $pos++;
                continue;
            }

            switch ($state) {
                case 'start':
                    if ($char === self::STRING_QUOTE) {
                        $state = 'string';
                        $pos++;
                    } elseif (ctype_digit($char) || $char === '.') {
                        $state = 'number';
                        $currentToken = $char;
                        $pos++;
                    } elseif (ctype_alpha($char)) {
                        $state = 'identifier';
                        $currentToken = $char;
                        $pos++;
                    } elseif (in_array($char, ['+', '-', '*', '/', '^', '(', ')', ','])) {
                        $tokens[] = $char;
                        $pos++;
                    } else {
                        $pos++;
                    }
                    break;

                case 'number':
                    if (ctype_digit($char) || $char === '.') {

                        $currentToken .= $char;

                        $pos++;
                    } else {
                        $tokens[] = $currentToken;
                        $currentToken = '';
                        $state = 'start';
                    }
                    break;

                case 'identifier':
                    if (ctype_alnum($char) || $char === '_') {
                        $currentToken .= $char;
                        $pos++;
                    } else {
                        $this->addIdentifier($tokens, $currentToken);
                        $currentToken = '';
                        $state = 'start';
                    }
                    break;

                case 'string':
                    if ($char === self::STRING_QUOTE) {
                        $tokens[] = self::STRING_QUOTE . $currentToken . self::STRING_QUOTE;
                        $currentToken = '';
                        $state = 'start';
                        $pos++;
                    } elseif ($char === '\\') {
                        $escapeNext = true;
                        $pos++;
                    } else {
                        $currentToken .= $char;
                        $pos++;
                    }
                    break;
            }
        }

        $this->finalizeToken($tokens, $currentToken, $state);
        return $tokens;
    }

    private function addIdentifier(array &$tokens, string $token): void
    {
        $lowerToken = strtolower($token);
        if (in_array($lowerToken, self::FUNCTIONS)) {
            $tokens[] = $lowerToken;
        } else {
            $tokens[] = $lowerToken; // Добавляем переменную в нижнем регистре
        }
    }

    private function finalizeToken(array &$tokens, string $token, string $state): void
    {
        if ($token !== '') {
            match ($state) {
                'number' => $tokens[] = $token,
                'identifier' => $this->addIdentifier($tokens, $token),
                'string' => throw new Exception("Незакрытая строка"),
                default => null
            };
        }
    }

    private function isStringLiteral(string $token): bool
    {
        return strlen($token) > 1 && $token[0] === self::STRING_QUOTE && $token[-1] === self::STRING_QUOTE;
    }

    // Преобразование в ОПЗ (без изменений)
    public function parseToRPN(array $tokens): array
    {
        $output = [];
        $stack = [];
        $operators = [
            '+' => 2,
            '-' => 2,
            '*' => 3,
            '/' => 3,
            '^' => 4,
            '(' => 0,
            ')' => 0
        ];

        foreach ($tokens as $token) {
            if ($this->isStringLiteral($token)) {
                $output[] = $token;
            } elseif (is_numeric($token)) {
                $output[] = $token;
            } elseif (in_array($token, self::FUNCTIONS)) {
                $stack[] = $token;
            } elseif ($token === '(') {
                $stack[] = $token;
            } elseif ($token === ')') {
                $this->processClosingBracket($output, $stack);
            } elseif ($token === ',') {
                $this->processComma($stack, $output);
            } elseif (isset($operators[$token])) {
                $this->processOperator($token, $operators, $stack, $output);
            } else {
                throw new Exception("Неизвестный токен: $token");
            }
        }

        while (!empty($stack)) {
            $op = array_pop($stack);

            if ($op === '(') {
                throw new Exception("Несбалансированные скобки");
            }

            $output[] = $op;
        }

        return $output;
    }

    private function processComma(array &$stack, array &$output): void
    {
        while (!empty($stack) && end($stack) !== '(') {
            $output[] = array_pop($stack);
        }

        if (empty($stack)) {
            throw new Exception("Лишняя запятая");
        }
    }

    private function processClosingBracket(array &$output, array &$stack): void
    {
        while (end($stack) !== '(') {
            $output[] = array_pop($stack);
            if (empty($stack)) {
                throw new Exception("Несбалансированные скобки");
            }
        }
        array_pop($stack);
        if (!empty($stack) && in_array(end($stack), self::FUNCTIONS, true)) {
            $output[] = array_pop($stack);
        }
    }

    private function processOperator(string $token, array $operators, array &$stack, array &$output): void
    {
        while (!empty($stack) &&
            ($operators[$token] <= ($operators[end($stack)] ?? 0))) {
            $output[] = array_pop($stack);
        }

        $stack[] = $token;
    }

    // Вычисление ОПЗ с поддержкой переменных
    public function evaluateRPN(array $rpn): mixed
    {
        $stack = [];
        foreach ($rpn as $token) {
            if ($this->isStringLiteral($token)) {
                $stack[] = substr($token, 1, -1);
            } elseif (is_numeric($token)) {
                $stack[] = (float)$token;
            } elseif (in_array($token, self::FUNCTIONS, true)) {
                $this->executeFunction($token, $stack);
            } elseif (in_array($token, ['+', '-', '*', '/', '^'])) {
                $this->executeOperator($token, $stack);
            } else {
                // Обработка переменных
                $var_name = strtolower($token);
                if (array_key_exists($var_name, $variables)) {
                    $stack[] = $variables[$var_name];
                } else {
                    throw new Exception("Переменная '$var_name' не определена");
                }
            }
        }

        return array_pop($stack);
    }

    private function executeOperator(string $op, array &$stack): void
    {
        $b = array_pop($stack);
        $a = array_pop($stack);

        $result = match ($op) {
            '+' => $a + $b,
            '-' => $a - $b,
            '*' => $a * $b,
            '/' => $a / $b,
            '^' => $a ** $b,
            default => throw new Exception("Неизвестный оператор: $op"),
        };

        $stack[] = $result;
    }

    private function executeFunction(string $fn, array &$stack): void
    {
        $args = [];
        $argCount = match ($fn) { // Укажите количество аргументов для функций
            'concat' => 2,
            'myfunc' => 3,
            default => 1
        };

        for ($i = 0; $i < $argCount; $i++) {
            array_unshift($args, array_pop($stack));
        }

        $exchange = static function ($a, $b, $c) {

            if ($a === 'min') {
                return $b;
            }

            return $c;
        };

        $result = match ($fn) {
            'sin' => sin($args[0]),
            'cos' => cos($args[0]),
            'sqrt' => sqrt($args[0]),
            'exp' => exp($args[0]),
            'concat' => $args[0] . $args[1],
            'myfunc' => $exchange($args[0], $args[1], $args[2]),
            default => throw new Exception("Неизвестная функция: $fn"),
        };

        $stack[] = $result;
    }
}

// Пример использования с переменными
try {
    //$formula = "sqrt(sqrt(2.5^2 + 3^2) * 4^2)";
    //$formula = 'concat("A", "B")';
    //$formula = '2*2+sqrt(5*6+sqrt(12+12))';
    $formula = 'myfunc("min", 1, sqrt(2.5)) + 3 + sqrt(sqrt(2.5) + 5^2)';
    $parser = new FormulaParser();
    $tokens = $parser->tokenize($formula);
    $rpn = $parser->parseToRPN($tokens);

    $variables = ['x' => 3, 'y' => 4];
    $result = $parser->evaluateRPN($rpn, $variables);

    echo "Формула: $formula\n";
    echo "Токены: " . implode(' ', $tokens) . "\n";
    echo "ОПЗ: " . implode(' ', $rpn) . "\n";
    echo "Результат: $result\n"; // 5

} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}