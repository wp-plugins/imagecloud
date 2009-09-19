<?php

/**
 * Class implementing all plugin functions
 * @author Joost
 */

class ic_Plugin
{
	private $cachedir;
	
	function __construct()
	{
		
		//get and create cache directory
		$this->cachedir = plugin_dir_path(__FILE__). "cache/";
		if(!file_exists($this->cachedir))
			@mkdir($this->cachedir, 0755);
		
		//add cloud tag filter
		add_filter('wp_generate_tag_cloud', array(&$this, 'onGenerateImageCloud'), 10, 3);
		
		//add Imagecloud shortcode
		add_shortcode('imagecloud', array(&$this, 'onImageCloudShortCode'));
		
		//signal any changes to the terms.
		add_action("delete_term", array(&$this, 'onTaxonomyChanged'));
		add_action("created_term", array(&$this, 'onTaxonomyChanged')); 
		add_action("edited_term", array(&$this, 'onTaxonomyChanged')); 
		add_action("set_object_terms", array(&$this, 'onTaxonomyChanged')); 
	}
	
	/**
	 * filter to word cloud that adds the new wordcloud
	 * @param $return
	 * @param $tags
	 * @param $args
	 * @return unknown_type
	 */
	function onGenerateImageCloud($return, $tags, $args)
	{
		if($args['format']=="image")
		{
	
			include_once('lib/ic_WordCloud.php');
			
			if(!isset($args['font']))
				$args['font'] = plugin_dir_path(__FILE__) . "fonts/arial.ttf";
			
			$wordCloud = new ic_WordCloud($args);
			$wordCloud->setTags($tags);
			
			//during debugging, make sure that the cache is cleared such that a new cloud is created each time
			//$this->onTaxonomyChanged("");
			
			$return = $wordCloud->getHTML($this->cachedir);
		}
		
		return $return;
	}
	
	/**
	 * adds the [Imagecloud] shortcode. Accepts all normal wp_tag_cloud arguments
	 * @param $atts
	 * @param $content
	 * @return unknown_type
	 */
	function onImageCloudShortCode($atts, $content = null)
	{
		//get arguments
		$args = wp_parse_args($atts, array('format' => 'image'));
		
		//make sure tag cloud generation doesn't print, but returns data
		$args['echo'] = false;
		
		return wp_tag_cloud($args);
	}
	
	/**
	 * clears the cache whenever something changes to any taxonomy
	 * @param $term unused
	 */
	function onTaxonomyChanged($term)
	{
		$cachefiles = glob($this->cachedir."*.*"); 
		if(!empty($cachefiles))
		{
			foreach($cachefiles as $file)
			{
				@unlink($file);
			}
		}
	}
}

