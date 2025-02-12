<?php namespace RPN;

use InvalidArgumentException;

class Functions
{

    protected $functions = [];

    public function register(...$args)
    {

        if(!isset($args[0])) {
            throw new InvalidArgumentException('Нет аргументов для регистрации функции');
        }

        if (
            isset($args[0], $args[1], $args[2]) &&
            is_string($args[0]) &&
            is_int($args[1]) &&
            is_callable($args[2])
        ) {
            $this->functions[strtolower($args[0])] = (object)[
                'args' => $args[1],
                'exec' => $args[2]
            ];
        }

        if (is_array($args[0])) {
            foreach ($args[0] as $fn) {
                $this->register(...$fn);
            }
        }
        if (is_string($args[0]) && class_exists($args[0])) {
            $obj = new $args[0];

            if ($obj instanceof FunctionsCollectionsInterface) {
                $this->register($obj());
            }
        }

        if ($args[0] instanceof FunctionsCollectionsInterface) {
            $this->register($obj());
        }

        return $this;
    }

    public function has($name)
    {
        $name = strtolower($name);
        if (isset($this->functions[$name])) {
            return true;
        }

        return false;
    }

    public function get($name)
    {
        $name = strtolower($name);

        if (!$this->has($name)) {
            throw new \Exception('not found function');
        }

        return $this->functions[$name];
    }

    public function execute(...$args)
    {

    }

}