<?php namespace RPN;

class StringFunctions implements FunctionsCollectionsInterface
{
    protected array $functions = [
        'concat' => 2,
    ];

    public function __invoke()
    {
        $output = [];

        foreach ($this->functions as $fn => $args) {
            $output[] = [$fn, $args, $this->$fn(...)];
        }

        return $output;
    }

    public function concat($a, $b)
    {
        return $a . $b;
    }

}