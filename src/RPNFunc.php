<?php namespace RPN;

use Closure;
use DomainException;
use Exception;
use InvalidArgumentException;

/**
 * Класс для расчета значения по обратной польской записи
 */
class RPNFunc
{

	protected array $list = [];

	public function get(string $name): array
	{
		if (!isset($this->list[$name]))
		{
			throw new InvalidArgumentException(sprintf('%s функция не зарегистрирована', $name));
		}

		return $this->list[$name];
	}

	public function has(string $name): bool
	{
		if (!isset($this->list[$name]))
		{
			return false;
		}

		return true;
	}

	public function register(string $name, int $arg, Closure $func): void
	{
		$this->list[$name] = [
			'args' => $arg,
			'func' => $func,
		];
	}

	public function registerCollection(array $list): void
	{
		foreach ($list as $item)
		{
			$this->register($item['name'], $item['args'], $item['func']);
		}
	}

	public function execute(string $name, array $args): float
	{
		$func = $this->get($name);

		try
		{
			return call_user_func_array($func['func'], $args);
		}
		catch (Exception)
		{
			throw new DomainException(sprintf('Произошла ошибка при обработки функции %s', $name));
		}
	}

}