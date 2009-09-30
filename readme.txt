=== ImageCloud ===

Contributors: reuzel
Tags: tag, tagcloud, image
Requires at least: 2.7?
Tested up to: 2.8.4
Stable tag: 1.1

This plugin adds a new format to the `wp_tag_cloud` function, allowing for image based -- Wordle-like -- tag clouds.

== Description ==

This plugin allows the creation of tag clouds based on GIF images. 

Each word in the taglist is converted to an image. The images may vary in size, orientation and color. The images are then positioned in a condense way, resulting in [Wordle](http://www.wordle.net "Wordle")-like tag-clouds.

To use the image-style tag-clouds set the format parameter of `wp_tag_cloud` to `image`. For convenience a shortcode is provided `[imagecloud]` that will insert the tag-cloud with the image format option set anywhere within a post or page. The imagecloud shortcode will accept all arguments that `wp_tag_cloud` accepts plus the few additional ones described below.

To control the image creation a few extra options are provided that may be provided with [`wp_tag_cloud`](http://codex.wordpress.org/Template_Tags/wp_tag_cloud "Template Tags/wp_tag_cloud") or the `[imagecloud]` shortcode. These are:

1. *fgcolor*: The color of the texts in the images. Must be in the format "#AABBCC".
1. *fgcolor2*: A second foreground color. When this is set, the tag text color will be a color between fgcolor and fgcolor2, depending on the tag count (occurance). Must be in the format "#AABBCC".
1. *bgcolor*: The background color of the texts. Must be in the format "#AABBCC".
1. *transparent*: set to true if you wish to make the image transparent. Do not forget to set the bgcolor also to prevent strange text-edge colors as the texts will be anti-aliased.
1. *margin*: set to the amount of pixels added around each tag.
1. *font*: set to the file location or url of the TrueType font (TTF) file to be used to create the tags.
1. *percentup*: percentage of words that is to be rotated 90 degrees.


== Installation ==

Installation of ImageCloud is straightforward:

1. Extract the zip-file in your plugins directory (typically '/wp-content/plugins/'). Or through the automatic install functions of WordPress.
1. Activate the plugin through the 'Plugins' menu in WordPress

After installation make sure you adapt your theme to use the image format as argument to `wp_tag_cloud`. Or use the [imagecloud] shortcode anywhere in a page or post.

Make sure you have proper tags on your post. One way to add these is by deriving them from the texts in your blog, for example by using the [Text2Tag](http://wordpress.org/extend/plugins/text2tag/ "Text2Tag Wordpress Plugin").

== Frequently Asked Questions == 

= Can you provide me with an example? =

Add the following text to a page to add the image-based tag-cloud. This cloud will have tags that have different shades of gray depending on their occurance.

[imagecloud fgcolor="CCCCCC" fgcolor2="000000" bgcolor="FFFFFF"]

= I don't like the cloud created. Can I recreate it? =

The current version has no specific option-page to do so. Clouds are regenerated when:

1. the corresponding taxonomy (tags) are changed.
1. The cache is empty. You can clear the cache by removing all files from the cache directory (inside the imagecloud directory in your plugins directory).

= What is the algorithm you use? =
Regrettably, the [Wordle](http://www.wordle.net "Wordle")  algorithms are not made public. Therefore I had to create one myself. The algorithm used is pretty straightforward. Images are rotated based by chance based on the percentup variable. All images are then positioned one by one around the center of the cloud. There is some randomness in the positioning as I've learned that this will actually improve the layout of the cloud.

This algorithm is very simple and improvements can definitly be made! Any suggestion is welcome...

== Screenshots ==

1. This screenshot shows an example cloud. This cloud is generated using the following shortcode: `[imagecloud bgcolor="000000" fgcolor="171512" fgcolor2="A6937F" smallest=8 largest=50 percentup=40 maxWidth="450"]`

== Changelog ==

= 1.1 =
* fixed query encoding bug. Special chars in words are now better supported.

= 1.0 =
* Initial version