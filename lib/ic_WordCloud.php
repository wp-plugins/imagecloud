<?php

	include_once("ic_Cloud.php");
	include_once("ic_Word.php");
	
	class ic_WordCloud extends ic_Cloud
	{
		private $tagArray;
		private $args;
		
		public function __construct($args = array())
		{
			//convert units to pt
			switch($args['unit'])
			{
				case "px":
					$args['smallest'] = $args['smallest'] / 96 * 25.4 / 0.35146 ; //divide by DPI to get inches, multiply by 25.4 to get mm, divide by 0.35146 to get points 
					$args['largest'] = $args['largest'] / 96 * 25.4 / 0.35146 ; //divide by DPI to get inches, multiply by 25.4 to get mm, divide by 0.35146 to get points
					$args['unit'] = "pt"; 
					break;
					
				case "em":
					$args['smallest'] *= 12; //assume 12pt base letter
					$args['largest'] *= 12;
					$args['unit'] = "pt";
					break; 
 			}
 			
 				
			//default values for word (cloud) specific properties
			$defs = array(
				'font' => '../fonts/arial.ttf',
				'fgcolor'=>"000000",
				'fgcolor2' => "",
				'bgcolor'=>"FFFFFF",
				'transparent'=>false,
				'percentup' => 20,
				'maxwidth' => -1,
				'margin' => 1
				);

			//merge the args with the default values
			$args = array_merge($defs, $args);
						
			//init parent
			parent::__construct($args['maxwidth']);
			
			//set object values
			$this->args = $args;
			
			//init tagArray
			$this->tagArray = array();
		}
		
		public function getArguments()
		{
			return $this->args;
		}
				
		/**
		 * sets the tags of the tagCloud
		 * @param $tagArray
		 * @return void
		 */
		public function setTags($tags)
		{
			//convert objects to array
			foreach($tags as $key=>$tag)
				$tags[$key] = wp_parse_args($tag);
				
			//calculate min and max of tag inputs
			$counts = array();
			foreach ($tags as $key => $tag )
				$counts[ $key ] = $tag['count'];
				
			$minCount = min($counts);
			$maxCount = max($counts);
						
			foreach($tags as $tag)
			{
				//get word values by merging cloud default values with tag specific values
				$vals = array_merge($this->args, $tag);
								
				//set angle. rotate box based on chance
				$p = rand(0,100);
				$vals['angle'] = $p < $this->args['percentup'] ? 90 : 0;
				
				//calculate ratio of size 
				if($maxCount-$minCount <= 0) 
					$ratio = .5;
				else
					$ratio = ($tag['count'] - $minCount) / ($maxCount - $minCount);
				
				//set font size
				$vals['size'] = $this->args['smallest'] + $ratio * ($this->args['largest'] - $this->args['smallest']);
				
				//calculate color
				if(isset($this->args['fgcolor2']) && $this->args['fgcolor2'] != "")
				{
					//color to hex array
					$rgb = str_split(substr($this->args['fgcolor'], -6),2);
					$rgb = array_map('hexdec', $rgb);
					$rgb2 = str_split(substr($this->args['fgcolor2'], -6),2);
					$rgb2 = array_map('hexdec', $rgb2);
					
					//calculate color in decimals
					$rgb[0] = $rgb[0] + $ratio * ($rgb2[0] - $rgb[0]);
					$rgb[1] = $rgb[1] + $ratio * ($rgb2[1] - $rgb[1]);
					$rgb[2] = $rgb[2] + $ratio * ($rgb2[2] - $rgb[2]);
					
					//set new color
					$vals['fgcolor'] = sprintf("%02X%02X%02X", $rgb[0],$rgb[1],$rgb[2]);
				}
				
				//create new word
				$word = new ic_Word($vals, $tag);
											
				//add the word
				$this->Add($word, false);
			}
		}
		
		function getHTML($cachedir = "")
		{

			//calculate cache file name
			$args = $this->args;
			ksort($args);
			$hash = md5(http_build_query($args));
			$cachefile = $cachedir . "{$args['taxonomy']}_$hash.html";
			
			//if cache exists return it
			if(file_exists($cachefile))
			{
				return file_get_contents($cachefile);
			}
			else
			{
				//give extra time (up to 5 minutes) for layout and image generation 
				$timeLimit = 5*60;
				$currentLimit = ini_get('max_execution_time');
				if($currentLimit!=0 && $currentLimit<$timeLimit)
					set_time_limit($timeLimit);
				
				//make sure cloud has been layout properly
				$this->layout();
				$this->moveTo(new ic_Point(0,0));
				
				//create opening tag for tag cloud
				$html = "<div class='tag-cloud'><div style='position: relative; height: {$this->height}px; width: {$this->width}px;'>";
				
				foreach($this->boxes as $box)
				{
					$html .= $box->getHTML($cachedir);
				}
			
				//close wordcloud html tags
				$html .= "</div></div>";
				
				//write the cache file (if caching dir is set)
				file_put_contents($cachefile, $html);
				
				return $html;
			}
		}
		
		
		
	}