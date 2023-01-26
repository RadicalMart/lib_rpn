<?php namespace RPN;

use DomainException;
use Exception;

/**
 * Класс для расчета значения по обратной польской записи
 */
class RPNCalc
{

	public RPNFunc $func;

	public function __construct()
	{
		$this->func = new RPNFunc;
	}

	public function registerFuncCollection(array $list): void
	{
		$this->func->registerCollection($list);
	}

	/**
	 * @param   string  $rpn
	 *
	 * @return float
	 */
	public function calc(string $rpn): float
	{
		$stack = [];
		$split = explode(' ', $rpn);
		foreach ($split as $item)
		{
			if ($item === '')
			{
				continue;
			}

			if (is_numeric($item))
			{
				$stack[] = $item;
				continue;
			}

			if ($this->func->has($item))
			{
				$func = $this->func->get($item);
				$args_count = $func['args'];
				$args = [];

				for ($i = 1; $i <= $args_count; $i++)
				{
					$args[] = array_pop($stack);
				}

				$stack[] = $this->func->execute($item, $args);
			}
			else
			{
				$right = array_pop($stack) ?? null;
				$left  = array_pop($stack) ?? null;

				if ($right === null || $left === null)
				{
					throw new DomainException('Неверное выражение!');
				}

				$stack[] = $this->calcOperator($left, $right, $item);
			}

		}

		return $stack[0];
	}

	protected function calcOperator(float $left, float $right, string $operator): float
	{
		switch ($operator)
		{
			case '-':
				return $left - $right;
			case '+':
				return $left + $right;
			case '*':
				return $left * $right;
			case '^':
				return $left ** $right;
			case '/':

				if ($right === 0)
				{
					throw new DomainException('Деление на ноль!');
				}

				return $left / $right;
			default:
				throw new DomainException('Неизвестный оператор ' . $operator);
		}
	}

}