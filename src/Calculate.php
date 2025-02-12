<?php namespace RPN;

class Calculate
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

    public function solve($rpn = [])
    {
        $stack = [];
        foreach ($rpn as $token) {
            if ($this->isStringLiteral($token)) {
                $stack[] = substr($token, 1, -1);
            } elseif (is_numeric($token)) {
                $stack[] = (float)$token;
            } elseif ($this->functions->has($token)) {
                $this->executeFunction($token, $stack);
            } elseif (in_array($token, ['+', '-', '*', '/', '^'])) {
                $this->executeOperator($token, $stack);
            }
        }

        return array_pop($stack);
    }

    protected function isStringLiteral(string $token): bool
    {
        return strlen($token) > 1 && $token[0] === self::STRING_QUOTE && $token[-1] === self::STRING_QUOTE;
    }

    protected function executeOperator(string $op, array &$stack): void
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

    protected function executeFunction(string $name, array &$stack): void
    {
        $args = [];
        $function = $this->functions->get($name);
        $arg_count = $function->args;

        for ($i = 0; $i < $arg_count; $i++) {
            array_unshift($args, array_pop($stack));
        }

        $callback = &$function->exec;

        $stack[] = $callback(...$args);
    }

}