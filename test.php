<?php

include __DIR__ . '/src/RPNBase.php';
include __DIR__ . '/src/RPNCalc.php';
include __DIR__ . '/src/RPNFunc.php';
include __DIR__ . '/src/RPNWiki.php';

use RPN\RPNBase;
use RPN\RPNCalc;
use RPN\RPNWiki;

$RPNBase = new RPNBase;
$RPNCalc = new RPNCalc;

$list = include __DIR__ . '/func.php';
$RPNCalc->registerFuncCollection($list);


echo "RPNBase:\n";
$rpn = $RPNBase->to($argv[1]);
echo "RPN: " . $rpn . "\n";
echo "Расчет: " . $RPNCalc->calc($rpn);


echo "\n\nRPNWiki:\n";
$RPNWiki = new RPNWiki($argv[1]);
echo "RPN: " . $RPNWiki->getOutstring() . "\n";
echo "Расчет: " . $RPNWiki->result;

