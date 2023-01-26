<?php
include __DIR__ . '/RPNBase.php';
include __DIR__ . '/RPNWiki.php';

$RPNBase = new RPNBase;

echo "RPNBase:\n";
$rpn = $RPNBase->stringToRpn($argv[1]);
echo "RPN: " . $rpn . "\n";
echo $RPNBase->calcFromRpn($rpn);


echo "\n\nRPNWiki:\n";
$RPNWiki = new RPNWiki($argv[1]);
echo "RPN: " . $RPNWiki->getOutstring() . "\n";
echo $RPNWiki->result;