<?php

class ic_Point
{
	public $x = 0;
	public $y = 0;
	
	function __construct($x,$y)
	{
		$this->x = $x;
		$this->y = $y;
	}
	
	function above(ic_Point $p)
	{
		return $this->y < $p->y;
	}
	
	function below(ic_Point $p)
	{
		return $this->y > $p->y;
	}
	
	function leftOf(ic_Point $p)
	{
		return $this->x < $p->x;
	}
	
	function rightOf(ic_Point $p)
	{
		return $this->x > $p->x;
	}
	
	function moveTo(ic_Point $p)
	{
		$this->x = $p->x;
		$this->y = $p->y;
	}
	
	function translate($x, $y)
	{
		$this->x += $x;
		$this->y += $y;
	}
	
	function compare(ic_Point $a, ic_Point $b)
	{
		//calc relative distance to origin (skip sqrt)
		$lenA = pow($a->x,2) + pow($a->y,2);
		$lenB = pow($b->x,2) + pow($b->y,2);
		
		//compare
		if($lenA==$lenB)
			return 0;
		else
			return $lenA > $lenB ? 1 : -1;
	}
}
?>