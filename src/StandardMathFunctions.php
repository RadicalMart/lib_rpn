<?php namespace RPN;

use InvalidArgumentException;

class StandardMathFunctions implements FunctionsCollectionsInterface
{
    // Список количества аргументов для каждой функции
    protected array $functions = [
        'ceil' => 1,
        'round' => 1,
        'min' => 2,
        'max' => 2,
        'sqrt' => 1,
        'sqr' => 1,
        'abs' => 1,
        'power' => 2,
        'factorial' => 1,
        'isPrime' => 1,
    ];

    /**
     * @return array
     */
    public function __invoke(): array
    {
        $output = [];

        foreach ($this->functions as $fn => $args) {
            $output[] = [$fn, $args, $this->$fn(...)];
        }

        return $output;
    }

    /**
     * Округление числа в большую сторону
     *
     * @param float $number
     * @return int|float
     */
    public function ceil(float $number): int|float
    {
        return ceil($number);
    }

    /**
     * Округление числа до ближайшего целого или с заданным количеством знаков после запятой
     *
     * @param float $number
     * @return float
     */
    public function round(float $number): float
    {
        return round($number);
    }

    /**
     * Нахождение минимального значения из набора чисел
     *
     * @param $a
     * @param $b
     * @return mixed
     */
    public function min($a, $b): int|float
    {
        return min($a, $b);
    }

    /**
     * Нахождение максимального значения из набора чисел
     *
     * @param $a
     * @param $b
     * @return mixed
     */
    public function max($a, $b): int|float
    {
        return max($a, $b);
    }

    /**
     * Квадратный корень числа
     *
     * @param float $number
     * @return float
     */
    public function sqrt(float $number): float
    {
        if ($number < 0) {
            throw new InvalidArgumentException('Нельзя вычислить квадратный корень отрицательного числа');
        }

        return sqrt($number);
    }

    /**
     * Возведение числа в квадрат
     *
     * @param float $number
     * @return float
     */
    public function sqr(float $number): float
    {
        return $number ** 2;
    }

    /**
     * Абсолютное значение числа
     *
     * @param float $number
     * @return float
     */
    public function abs(float $number): float
    {
        return abs($number);
    }

    /**
     * Возведение числа в степень
     *
     * @param float $a
     * @param float $b
     * @return float
     */
    public function pow(float $a, float $b): float
    {
        return $a ** $b;
    }

    /**
     * Факториал числа
     *
     * @param int $number
     * @return int
     */
    public function factorial(int $number): int
    {
        if ($number < 0) {
            throw new InvalidArgumentException('Факториал определен только для неотрицательных целых чисел');
        }

        $result = 1;
        for ($i = 2; $i <= $number; $i++) {
            $result *= $i;
        }

        return $result;
    }

    /**
     * Проверка, является ли число простым
     *
     * @param int $number
     * @return bool
     */
    public function isPrime(int $number): bool
    {
        if ($number <= 1) {
            return false;
        }
        for ($i = 2; $i <= sqrt($number); $i++) {
            if ($number % $i === 0) {
                return false;
            }
        }
        return true;
    }

}