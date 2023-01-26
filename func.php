<?php

return [

	[
		'name' => 'ceil',
		'args' => 1,
		'func' => static function (float $a) {
			return ceil($a);
		}
	],
	[
		'name' => 'max',
		'args' => 2,
		'func' => static function (float $a, float $b) {
			return max($a, $b);
		}
	],
	[
		'name' => 'min',
		'args' => 2,
		'func' => static function (float $a, float $b) {
			return min($a, $b);
		}
	],
	[
		'name' => 'round',
		'args' => 2,
		'func' => static function (float $a, float $b) {
			return round($a, $b);
		}
	],
	[
		'name' => 'percent',
		'args' => 2,
		'func' => static function (float $a, float $b) {
			return $a * ($b / 100);
		}
	],

];
