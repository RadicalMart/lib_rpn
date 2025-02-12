<?php namespace RPN;

class Formula
{

    protected $functions;

    protected $calculate;

    protected $rpn;

    public function __construct(...$args)
    {
        $this->functions = new Functions;
        $this->calculate = new Calculate($this->functions);
        $this->rpn = new RPN($this->functions);
    }

    public function getFunctions()
    {
        return $this->functions;
    }

    public function toRPN(...$args)
    {

    }

    public function solveFromRPN(...$args)
    {
    }

    public function solve(...$args)
    {
        $output = null;

        if (is_array($args[0])) {
            foreach ($args[0] as $formula) {
                $output[] = $this->solve(...$formula);
            }
        }

        if (is_string($args[0])) {
            $output = $this->calculate->solve(
                $this->rpn->to($args[0])
            );
        }

        if(is_null($output)) {
            throw new \Exception('Invalid formula');
        }
        
        return $output;
    }

}