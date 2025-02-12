<?php

include __DIR__ . '/../src/RPN.php';
include __DIR__ . '/../src/Formula.php';
include __DIR__ . '/../src/Functions.php';
include __DIR__ . '/../src/Calculate.php';


$rpn = new \RPN\RPN();
$rpn->getFunctions()
    ->register('sqrt', 1, static function ($a) {
        return sqrt($a);
    })
    ->register('min', 2, static function ($a, $b) {
        return $a > $b ? $b : $a;
    });

$formula = 'sqrt(sqrt(4^2+5)+8*4)+min(3,5)';
$tokens = $rpn->tokenize($formula);


echo "Токены: " . implode(' ', $tokens) . "\n";

$rpn = $rpn->to($formula);
echo "ОПЗ: " . implode(' ', $rpn) . "\n";
