<?php namespace RPN;

// TODO сделать поддержку сложных аргументов для функций, по сути надо продублировать механизм скобок для функций и вести отдельный стек для них

/**
 * Класс для преобразование традиционной математической записи формул в обратную польскую нотацию через конечные автоматы
 */
class RPNBase
{
	private const UNARY_MINUS = '~';

	private const OPEN_BRACKET = '(';

	private const CLOSE_BRACKET = ')';

	private const EXPONENTIATION = '^';

	private const RIGHT_ASSOCIATIVE_EXPRESSION = [
		self::EXPONENTIATION, self::UNARY_MINUS
	];

	protected string $state = ''; // текущее состояние

	protected string $state_prev = ''; // предыдущие состояние

	protected array $output = []; // выходящая строка

	protected string $tmp = ''; // строка, которая накапливает символы, нужна для состояний

	protected array $stack = []; // стек для обратной польской записи

	protected bool $func = false; // флаг, чтобы понять что мы находимся внутри функции

	protected array $vars = []; // переменные для состояний

	protected array $operators = [ // базовые операторы
		'^' => 3,
		'*' => 2,
		'/' => 2,
		'+' => 1,
		'-' => 1
	];

	/**
	 * Преобразование математический записи формулы в обратную польскую запись
	 *
	 * @param   string  $string
	 *
	 * @return string
	 */
	public function to(string $string): string
	{

		$this->state0();

		for ($i = 0; $i < strlen($string); $i++)
		{
			if ($string[$i] === ' ')
			{
				continue;
			}

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

			if ($string[$i] === self::OPEN_BRACKET)
			{
				$state = 'state3';
			}

			if ($string[$i] === self::CLOSE_BRACKET)
			{
				$state = 'state4';
			}

			if (isset($this->operators[$string[$i]]))
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

	/**
	 * Начальное состояние
	 *
	 * @return void
	 */
	protected function state0(): void
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

	/**
	 * Состояние: ввода числа
	 *
	 * @param   string  $c
	 *
	 * @return void
	 */
	protected function state1(string $c): void
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

	protected function state1Output(): void
	{
		$this->output[] = $this->tmp;
		$this->tmp      = '';
		//$this->vars = [];
	}

	protected function state2Input(): void
	{
		$this->tmp = '';
	}

	/**
	 * Состояние: ввод наименования функции
	 *
	 * @param   string  $c
	 *
	 * @return void
	 */
	protected function state2(string $c): void
	{
		$this->tmp .= $c;
	}

	protected function state2Output(): void
	{
		$this->stack[] = $this->tmp;
		$this->tmp     = '';
	}

	protected function state3Input(): void
	{
		if ($this->state_prev === 'state2')
		{
			$this->func = true;
		}
	}

	/**
	 * Состояние: открывающая скобка
	 *
	 * @param   string  $c
	 *
	 * @return void
	 */
	protected function state3(string $c): void
	{
		$this->stack[] = $c;
	}

	/**
	 * Состояние: закрытие скобки
	 *
	 * @param   string  $c
	 *
	 * @return void
	 */
	protected function state4(string $c): void
	{

		if ($this->func)
		{

			array_pop($this->stack);
			$func           = array_pop($this->stack); // удаляем скобку (
			$this->output[] = $func;
            $this->func = false;

			return;
		}

		$stack = array_reverse($this->stack);

		foreach ($stack as $key => $item)
		{
			unset($stack[$key]);

			if ($item === self::OPEN_BRACKET)
			{
				$this->stack = array_reverse($stack);

				return;
			}

			$this->output[] = $item;
		}
	}

	protected function state4Output(): void
	{
		$this->func = false;
	}

	/**
	 * Состояние: ввод операторов
	 *
	 * @param   string  $c
	 *
	 * @return void
	 */
	protected function state5(string $c): void
	{
		$stack = array_reverse($this->stack);

		foreach ($stack as $key => $item)
		{
			if ($item === self::OPEN_BRACKET)
			{
				break;
			}

			if (in_array($item, self::RIGHT_ASSOCIATIVE_EXPRESSION, true) && $item === $c)
			{
				break;
			}

			if ($this->operators[$item] < $this->operators[$c])
			{
				break;
			}

			$this->output[] = $item;
			unset($stack[$key]);
		}

		$this->stack   = array_reverse($stack);
		$this->stack[] = $c;
	}

	/**
	 * Заключительное состояние
	 *
	 * @return void
	 */
	protected function state6(): void
	{
		while ($this->stack)
		{
			$this->output[] = array_pop($this->stack);
		}
	}

}