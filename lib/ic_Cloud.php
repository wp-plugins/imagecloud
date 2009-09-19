<?php

include_once "ic_Box.php";

class ic_Cloud extends ic_Box
{
	protected $boxes;
	protected $points;
	public $maxWidth;
	
	public function __construct($maxWidth = -1)
	{
		parent::__construct(0,0);
		$this->boxes = array();
		$this->points = array();
		$this->maxWidth = $maxWidth;
	}
	
	/**
	 * Adds a ic_Box to the cloud
	 * @param ic_Box $b ic_Box to place
	 * @param $layout set to true to give to ic_Box a position in the cloud
	 */
	public function add(ic_Box $b, $layout = true)
	{
		//add the ic_Box
		array_push($this->boxes, $b);
		$b->inPosition = false;
		$b->parent = $this;
		
		if($layout)
			$this->findSpot($b);
	}
	
	/**
	 * places all unplaced boxes in the wordcloud
	 */
	public function layout()
	{
		foreach($this->boxes as $b)
		{
			if(!$b->inPosition)
			{
				$this->findSpot($b);
			}
		}
	}
	
	/**
	 * Places a single ic_Box in the cloud
	 * @param $b ic_Box to place
	 * @return true if placement was succesfull, false otherwise
	 */
	private function findSpot(ic_Box $b)
	{
		$spotFound = false;
		
		if(count($this->points)==0)
		{
			$sp = new ic_Point(0,0);
			$spotFound = $this->checkSpot($b, $sp);
		}
		else
		{
			//extend list of possible attachpoint with all corners of the ic_Box
			$ps = array();
			foreach($this->points as $p)
			{
				$p1 = new ic_Point($p->x - $b->width - 1, $p->y - $b->height - 1);
				$p2 = new ic_Point($p->x, $p->y - $b->height - 1);
				$p3 = new ic_Point($p->x - $b->width - 1, $p->y);
				$p4 = new ic_Point($p->x + 1, $p->y + 1);
				array_push($ps, $p1, $p2, $p3, $p4);
			}
			
			//sort list to have ic_Point closest to origin first 
			usort($ps, array("ic_Cloud", "comparePoints"));
			
			//find spot using randomized chunks
			$pss = array_chunk($ps, 20, false);
			foreach($pss as $aps)
			{
				shuffle($aps);
				//go and find closest fitting spot
				foreach($aps as $ap)
				{
					$b->moveTo($ap);
					if($this->checkSpot($b))
					{
						$spotFound = true;
						break 2;
					}
				}
			}
		}
		
		if($spotFound)
		{
			//mark ic_Box as in position
			$b->inPosition = true;
			
			//add the ic_Box attachpoints
			$this->points = array_merge($this->points, $b->corners());
					
			//recalculate (cloud)ic_Box boundaries
			$this->ul->x = min($this->ul->x, $b->ul->x);
			$this->ul->y = min($this->ul->y, $b->ul->y);
			$this->lr->x = max($this->lr->x, $b->lr->x);
			$this->lr->y = max($this->lr->y, $b->lr->y);
		
			//recalculate (cloud)ic_Box size
			$this->height = $this->lr->y - $this->ul->y;
			$this->width = $this->lr->x - $this->ul->x;
		}
		
		return $spotFound;
	}
	
	/**
	 * checks if a ic_Box can be placed in the cloud on the given location
	 * @param $b ic_Box to place
	 * @param $p ic_Point selected for placement
	 * @return true/false
	 */
	private function checkSpot(ic_Box $b)
	{
		//validate if the cloud maxwidth isn't violated
		if($this->maxWidth > 0)
		{
			//calculate cloud ic_Box boundaries
			$lx = min($this->ul->x, $b->ul->x);
			$rx = max($this->lr->x, $b->lr->x);
			
			if(($rx - $lx) > $this->maxWidth)
				return false;
		}
		
		//validate if the ic_Box doesn't overlap other boxes
		$ok = true;
		foreach($this->boxes as $tb)
		{
			if($tb->inPosition && $b->overlaps($tb))
			{
				$ok = false;
				break;
			}
		}
		
		return $ok;
	}
	
	function comparePoints(ic_Point $a, ic_Point $b)
	{
		//calc relative distance to origin (skip sqrt)
		$lenA = pow($a->x,2) + pow(2 * $a->y,2);
		$lenB = pow($b->x,2) + pow(2 * $b->y,2);
		
		//compare
		if($lenA==$lenB)
			return 0;
		else
			return $lenA > $lenB ? 1 : -1;
	}

	function moveTo(ic_Point $point)
	{
		$dx = $point->x - $this->ul->x;
		$dy = $point->y - $this->ul->y;
		
		$this->translate($dx, $dy);
	}
	
	function translate($dx, $dy)
	{
		parent::translate($dx, $dy);

		foreach($this->boxes as $box)
		{
			$box->translate($dx, $dy);
		}
	}
}
?>