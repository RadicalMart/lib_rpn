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
    ->register(\RPN\StringFunctions::class);

$rows = [
    '6*2+sqrt(4^sqrt(4^2))',
    'concat("1", "2")+5',
];

foreach ($rows as $row) {
    $result = $formula->solve($row);
    echo "Формула: " . $row . "\n";
    echo "Результат: " . $result . "\n\n";
}

