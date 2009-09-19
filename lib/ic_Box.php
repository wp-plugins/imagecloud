<?php

include_once "ic_Point.php";

class ic_Box
{
	/**
	 * upper left corner
	 * @var ic_Point
	 */
	public $ul;
	
	/**
	 * lower right corner
	 * @var ic_Point
	 */
	public $lr;
	
	/**
	 * height of the box
	 * @var int
	 */
	public $height;
	
	/**
	 * width of the box
	 * @var int
	 */
	public $width;
	
	/**
	 * owner of the box (typically a Cloud)
	 * @var Cloud
	 */
	public $parent;
	
	/**
	 * marker to indicate wether this box has been placed in a Cloud
	 * @var bool
	 */
	public $inPosition;
	
	/**
	 * create the box
	 * @param $height
	 * @param $width
	 * @return Box
	 */
	function __construct($height, $width)
	{
		$this->ul = new ic_Point(0,0);
		$this->lr = new ic_Point($width, $height);
		$this->height = $height;
		$this->width = $width;
		$this->inPosition = false;
	}
	
	/**
	 * move the box to a specific coordinate
	 * @param $p
	 * @return unknown_type
	 */
	function moveTo(ic_Point $p)
	{
		$this->ul = clone $p;
		$this->lr = clone $p;
		$this->lr->translate($this->width, $this->height); 
	}
	
	/**
	 * move the box
	 * @param $x
	 * @param $y
	 * @return unknown_type
	 */
	function translate($x, $y)
	{
		$this->ul->translate($x,$y);
		$this->lr->translate($x,$y);
	}
		
	/**
	 * signal if this box overlaps with the provided box
	 * @param $b Box
	 * @return bool
	 */
	function overlaps(ic_Box $b)
	{
		return !(
				$b->lr->above($this->ul) ||
			    $b->lr->leftOf($this->ul) ||
			    $b->ul->below($this->lr) ||
			    $b->ul->rightOf($this->lr)
			    );
	}
	
	/**
	 * gives the coordinates of all corners of this box
	 * @return array of ic_Point
	 */
	function corners()
	{
		$points = array();
		$points[] = clone $this->ul;
		$points[] = new ic_Point($this->ul->x, $this->lr->y);
		$points[] = clone $this->lr;
		$points[] = new ic_Point($this->lr->x, $this->ul->y);
		return $points;
	}
}

?>