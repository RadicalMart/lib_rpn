<?php namespace RPN;

use Exception;

class RPN
{

    protected const STRING_QUOTE = '"';

    protected $functions;

    public function __construct($functions = null)
    {
        if (is_null($functions)) {
            $this->functions = new Functions;
        } else {
            $this->functions = $functions;
        }
    }

    public function getFunctions()
    {
        return $this->functions;
    }

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
        $tokens[] = $lowerToken;

        /*
        if ($this->functions->has($lowerToken)) {
            $tokens[] = $lowerToken;
        } else {
            $tokens[] = $lowerToken;
        }*/
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

    public function to($formula)
    {
        $tokens = $this->tokenize($formula);

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
            } elseif ($this->functions->has($token)) {
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

    protected function isStringLiteral(string $token): bool
    {
        return strlen($token) > 1 && $token[0] === self::STRING_QUOTE && $token[-1] === self::STRING_QUOTE;
    }


    protected function processClosingBracket(array &$output, array &$stack): void
    {
        while (end($stack) !== '(') {
            $output[] = array_pop($stack);
            if (empty($stack)) {
                throw new Exception("Несбалансированные скобки");
            }
        }
        array_pop($stack);
        if (!empty($stack) && $this->functions->has(end($stack))) {
            $output[] = array_pop($stack);
        }
    }

    protected function processComma(array &$stack, array &$output): void
    {
        while (!empty($stack) && end($stack) !== '(') {
            $output[] = array_pop($stack);
        }

        if (empty($stack)) {
            throw new Exception("Лишняя запятая");
        }
    }

    protected function processOperator(string $token, array $operators, array &$stack, array &$output): void
    {
        while (!empty($stack) &&
            ($operators[$token] <= ($operators[end($stack)] ?? 0))) {
            $output[] = array_pop($stack);
        }

        $stack[] = $token;
    }

}