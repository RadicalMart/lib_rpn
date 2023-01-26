<?php
// реализация обратной польской записи через конечные автоматы
// TODO сделать поддержку сложных аргументов для функций
// по сути надо продублировать механизм скобок для функций и вести отдельный стек для них

class RPNBase
{

	protected $state = ''; // текущее состояние
	protected $state_prev = ''; // предыдущие состояние
	protected $output = []; // выходящяя строка
	protected $tmp = ''; // строка, которая накапливает символы, нужна для состояний
	protected $stack = []; // стек для обратной польской записи
	protected $func = false; // флаг, чтобы понять что мы находимся внутри функции
	protected $vars = []; // переменные для состояний
	protected $operators = [ // базовые операторы
		'^' => 3,
		'*' => 2,
		'/' => 2,
		'+' => 1,
		'-' => 1
	];

	public function stringToRpn($string)
	{
		$state = 'state0';

		for ($i = 0; $i < strlen($string); $i++)
		{
			if (is_numeric($string[$i]))
			{
				$state = 'state1';
			}

			if (
				$string[$i] === ',' ||
				$string[$i] === '.'
			)
			{
				$state = 'state1';
			}

			if (preg_match('#[a-zA-Z]#', $string[$i]))
			{
				$state = 'state2';
			}

			if ($string[$i] === '(')
			{
				$state = 'state3';
			}

			if ($string[$i] === ')')
			{
				$state = 'state4';
			}

			if (in_array($string[$i], ['*', '/', '+', '-', '^']))
			{
				$state = 'state5';
			}

			if ($state !== $this->state_prev)
			{
				$state_prev_out   = $this->state_prev . 'Output';
				$state_next_input = $state . 'Input';

				if (method_exists($this, $state_prev_out))
				{
					$this->$state_prev_out();
				}

				if (method_exists($this, $state_next_input))
				{
					$this->$state_next_input();
				}

				$this->state_prev = $state;

			}

			//echo "{$state}\n";
			$this->$state($string[$i]);
			$this->state = $state;

		}

		$state_prev_out = $this->state . 'Output';
		if (method_exists($this, $state_prev_out))
		{
			$this->$state_prev_out();
		}

		$this->state6();

		return implode(' ', $this->output);
	}

	protected function state0()
	{
		$this->state      = '';
		$this->state_prev = '';
		$this->output     = [];
		$this->tmp        = '';
		$this->stack      = [];
		$this->func       = false;
		$this->vars       = [];
	}

	protected function state1Input()
	{
		$this->tmp = '';
	}

	protected function state1($c)
	{
		if ($c === ',')
		{
			$c = '.';

			if (
				$this->func
			)
			{
				$this->state1Output();

				return;
			}
		}

		$this->tmp .= $c;
	}

	protected function state1Output()
	{
		$this->output[]   = $this->tmp;
		$this->tmp        = '';
		$this->state1Vars = [];
	}

	protected function state2Input()
	{
		$this->tmp = '';
	}

	protected function state2($c)
	{
		$this->tmp .= $c;
	}

	protected function state2Output()
	{
		$this->stack[] = $this->tmp;
		$this->tmp     = '';
	}

	protected function state3Input()
	{
		if ($this->state_prev === 'state2')
		{
			$this->func = true;
		}
	}

	protected function state3($c)
	{
		$this->stack[] = $c;
	}

	protected function state4($c)
	{

		if ($this->func)
		{

			array_pop($this->stack);
			$func           = array_pop($this->stack); // удаляем скобку (
			$this->output[] = $func;

			return;
		}

		$stack = array_reverse($this->stack);

		foreach ($stack as $key => $char)
		{
			unset($stack[$key]);

			if ($char === '(')
			{
				$this->stack = array_reverse($stack);

				return;
			}

			$this->output[] = $char;
		}
	}

	protected function state4Output()
	{
		$this->func = false;
	}

	protected function state5($c)
	{
		$last = array_pop($this->stack);

		if (
			isset($this->operators[$last], $this->operators[$c]) &&
			($this->operators[$c] === $this->operators[$last])
		)
		{
			$this->output[] = $last;
		}
		else
		{
			$this->stack[] = $last;
		}

		$this->stack[] = $c;
	}


	protected function state6()
	{
		while ($this->stack)
		{
			$this->output[] = array_pop($this->stack);
		}
	}

	protected function calc($string)
	{
		return $this->calcFromRpn($this->stringToRpn($string));
	}

	protected function calcOperator($left, $right, $operator)
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
			case 'max':
				return $left > $right ? $left : $right;
			case 'min':
				return $left < $right ? $left : $right;
			default:
				throw new DomainException('Неизвестный оператор ' . $operator);
		}
	}

	public function calcFromRpn($rpn)
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

			$right = array_pop($stack) ?? null;
			$left  = array_pop($stack) ?? null;

			if ($right === null || $left === null)
			{
				throw new DomainException('Неверное выражение!');
			}

			$stack[] = $this->calcOperator($left, $right, $item);
		}

		return $stack[0];
	}
}