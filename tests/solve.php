<?php

include __DIR__ . '/../src/RPN.php';
include __DIR__ . '/../src/Formula.php';
include __DIR__ . '/../src/Functions.php';
include __DIR__ . '/../src/Calculate.php';
include __DIR__ . '/../src/FunctionsCollectionsInterface.php';
include __DIR__ . '/../src/StandardMathFunctions.php';
include __DIR__ . '/../src/StringFunctions.php';

$formula = new \RPN\Formula();

$formula->getFunctions()
    ->register(\RPN\StandardMathFunctions::class)
    ->register(\RPN\StringFunctions::class)
    ->register('myfunc', 1, static function ($a) {
        return (float)$a * 3;
    });

$rows = [
    '6*2+sqrt(4^sqrt(4^2))',
    'concat("1", "2")+5',
    'sqrt(sqrt(2^2) + 5/2*4)',
    'myfunc(5) + 3',
];

foreach ($rows as $row) {
    $result = $formula->solve($row);
    echo "Формула: " . $row . "\n";
    echo "Результат: " . $result . "\n\n";
}
