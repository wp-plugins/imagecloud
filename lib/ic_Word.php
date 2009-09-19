<?php

include_once "ic_Box.php";

class ic_Word extends ic_Box implements IteratorAggregate
{
	private $values;
	private $box;
	public $tag;
	
	public function __construct($args, $tag)
	{
		//set tag
		$this->tag = $tag;
		
		//set word values
		$defaults = array('name'=>$tag['name'], 'size'=>$tag['count'], 'margin'=>1, 'angle'=>0, 'fgcolor'=>"000000", 'bgcolor'=>"FFFFFF", 'font'=>"../fonts/arial.ttf", 'transparent'=>false);
		$args2 = array_intersect_key($args, $defaults);
		$this->values = array_merge($defaults, $args2);
		
		extract($this->values);
		
		$this->box = $this->calculateTextBox($size, $angle, $font, $name);
		parent::__construct($this->box['height']+1+2*$margin, $this->box['width']+2+2*$margin);	
	}
	
	public function __set($name, $value)
	{
		if(array_key_exists($name, $this->values))
		{
			$this->values[$name] = $value;
			
			//recalculate
			extract($this->values);
			$this->box = $this->calculateTextBox($size, $angle, $font, $name);
			$this->height = $this->box['height']+1+2*$margin;
			$this->width = $this->box['width']+2+2*$margin;
			$this->lr = new ic_Point($this->ul->x + $this->width, $this->ul->y + $this->height);
			$this->inPosition = false;
		}
	}
	
	public function __get($name)
	{
		return $this->values[$name];
	}
	
	public function __isset($name)
	{
		return isset($this->values[$name]);
	}
	
	public function __unset($name)
	{
		unset($this->values[$name]);
	}
	
	public function getIterator() {
        return new ArrayIterator($this->values);
    }
		
	public function getArguments()
	{
		return $args;
	}
	
	private function calculateTextBox($font_size, $font_angle, $font_file, $text) 
	{
	    $box = imagettfbbox($font_size, $font_angle, $font_file, $text);
	
	    $min_x = min(array($box[0], $box[2], $box[4], $box[6]));
	    $max_x = max(array($box[0], $box[2], $box[4], $box[6]));
	    $min_y = min(array($box[1], $box[3], $box[5], $box[7]));
	    $max_y = max(array($box[1], $box[3], $box[5], $box[7]));
	    
	    //correction in case of hanging letters...
	    if(phpversion() == "5.2.10" && count(array_intersect(str_split($text), str_split("gqfpj")))>0)
	    {
		    $boxhanging = imagettfbbox($font_size, $font_angle, $font_file, "Hgqfpj");
		    $boxnohanging = imagettfbbox($font_size, $font_angle, $font_file, "H");
		    $hangSize = ($boxhanging[5] - $boxhanging[1]) - ($boxnohanging[5] - $boxnohanging[1]);
	    }
	    else
	    	$hangSize = -1; 
		
	    return array(
	        'left' => ($min_x >= -1) ? -abs($min_x + 1) : abs($min_x + 2),
	        'top' => abs($min_y-$hangSize),
	        'width' => $max_x - $min_x,
	        'height' => $max_y - $min_y,
	        'box' => $box
	    );
	}
	
	public function getImage()
	{
		extract($this->values);
		
		//create the image
		$box = $this->calculateTextBox($size, 0, $font, $name);
		$h = $box["height"]+1+2*$margin;
		$w = $box["width"]+2+2*$margin;
		$image = imagecreatetruecolor($w,$h);
		imageantialias($image, true);
		
		//create the colors
		$rgb = str_split(substr($fgcolor, -6),2);
		$rgb = array_map('hexdec', $rgb);
		$fgImColor = imagecolorallocate($image,$rgb[0],$rgb[1],$rgb[2]);
		
		$rgb = str_split(substr($bgcolor, -6),2);
		$rgb = array_map('hexdec', $rgb);
		$bgImColor = imagecolorallocate($image,$rgb[0],$rgb[1],$rgb[2]);
						
		//fill image with background color
		imagefill($image, 0,0, $bgImColor);
 	 	
 	 	//write text
 	 	imagettftext($image,
               $size,
               0,
               $box["left"]+$margin,
               $box["top"]+$margin,
               $fgImColor,
               $font,
               $name);

        //rotate
        if($angle != 0)
        	$image = imagerotate($image, $angle, $bgImColor);

        //set transparent color
        if($transparent)
        	imagecolortransparent($image, $bgImColor);
        
        //return image
        return $image;
	}
	
	function getHTML($cachedir)
	{
		$args = $this->values;
		ksort($args);
		$hash = md5(http_build_query($args));
		$cachefile = "{$args['name']}_$hash.gif";
				
		//create image if it does not exist
		if(!file_exists($cachedir . $cachefile))
		{
			$im = $this->getImage();
			
			imagegif($im, $cachedir . $cachefile);
			imagedestroy($im);
		}
		
		//get image html info
		$url = plugin_dir_url(dirname(__FILE__)."../") . "cache/" . $cachefile;
		$height = $this->height;
		$width = $this->width;
		$top = $this->ul->y;
		$left = $this->ul->x;
		
		//get link html info
		$rel = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) ? ' rel="tag"' : '';
		$link = ('#' != $this->tag['link']) ? esc_url( $this->tag['link'] ) : '#';
		$id = $this->tag['id'];
		$count = $this->tag['count'];
		if(isset($this->tag['topic_count_text_callback']))
			$title = $args['name'] . " " .  esc_attr( $this->tag['topic_count_text_callback']($count));
		else
			$title = $args['name'];
		
		//create html
		$img = "<img src='$url' alt='$title' style='border: 0px none; height:{$height}px; width:{$width}px;' />";
		$a = "<a href='$link' class='tag-link-$id tag-link' style='top:{$top}px; left:{$left}px; border: 0px none; position:absolute;' title='$title'$rel >$img</a>";
		
		return $a;
	}
}
?>