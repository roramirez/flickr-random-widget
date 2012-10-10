<?php
/*
Plugin Name: Flickr Random Widget
Description: A widget which will display from your Flickr random photostream.
Author: Rodrigo Ramirez Norambuena
Version: 0.1
Author URI: http://decipher.blackhole.cl/

This plugins is adaption of http://wordpress.org/extend/plugins/flickr-widget/
with idea http://stephentrepreneur.wordpress.com/2008/05/01/random-flickr-on-wordpress/

Changelog
0.1 = First public release.
*/

function widget_random_flickr($args) {
	extract($args);

	$options = get_option('widget_random_flickr');
	if( $options == false ) {
		$options[ 'title' ] = 'Flickr Photos';
		$options[ 'items' ] = 3;
		$options[ 'userid' ] = '27352733@N00';

	}
	$title = empty($options['title']) ? __('Flickr Photos') : $options['title'];
  $userid = empty($options['userid']) ? __('27352733@N00') : $options['userid'];
	$items = $options[ 'items' ];
	$apikey = $options[ 'apikey' ];

	if ( empty($items) || $items < 1 || $items > 10 ) $items = 3;
	
  $url = "http://api.flickr.com/services/rest/?method=flickr.people.getPublicPhotos&per_page=500&api_key=".$apikey."&user_id=".$userid;
	
  $curl = curl_init ($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$xmlPhotos = curl_exec ($curl);
	curl_close($curl); 

	$xml = simplexml_load_string($xmlPhotos);

  $countPhotos = count($xml->photos->photo);

	
  $out = '';
	for ($i=0; $i < $items; $i++) {

		$randomImageID = rand(0, $countPhotos - 1);
    $imgId =  $xml->photos->photo[$randomImageID][id];
    $imgSecret =  $xml->photos->photo[$randomImageID][secret];
    $imgServer =  $xml->photos->photo[$randomImageID][server];
    $imgFarm =  $xml->photos->photo[$randomImageID][farm];
    $imgTitle =  $xml->photos->photo[$randomImageID][title];
		$imgURL = "http://farm".$imgFarm.".static.flickr.com/".$imgServer."/".$imgId."_".$imgSecret."_t.jpg";
		$linkURL = "http://flickr.com/photos/$userid/".$imgId; 
    $out .= "<a href='$linkURL' target='_new'><img src='$imgURL' alt='$imgTitle'/></a><br /><br /> ";
	}


	$flickr_home = "http://flickr.com/photos/$userid";
	?>

	<?php echo $before_widget; ?>
	<?php echo $before_title . $title . $after_title; ?>
<!-- Start of Flickr Badge -->
<style type="text/css">
#flickr_badge_source_txt {padding:0; font: 11px Arial, Helvetica, Sans serif; color:#666666;}
#flickr_badge_icon {display:block !important; margin:0 !important; border: 1px solid rgb(0, 0, 0) !important;}
#flickr_icon_td {padding:0 5px 0 0 !important;}
.flickr_badge_image {text-align:center !important;}
.flickr_badge_image img {border: 1px solid black !important;}
#flickr_badge_uber_wrapper {width:150px;}
#flickr_www {display:block; text-align:center; padding:0 10px 0 10px !important; font: 11px Arial, Helvetica, Sans serif !important; color:#3993ff !important;}
#flickr_badge_uber_wrapper a:hover,
#flickr_badge_uber_wrapper a:link,
#flickr_badge_uber_wrapper a:active,
#flickr_badge_uber_wrapper a:visited {text-decoration:none !important; background:inherit !important;color:#3993ff;}
#flickr_badge_wrapper {background-color:#ffffff;border: solid 1px #000000}
#flickr_badge_source {padding:0 !important; font: 11px Arial, Helvetica, Sans serif !important; color:#666666 !important;}
</style>
<table id="flickr_badge_uber_wrapper" cellpadding="0" cellspacing="10" border="0"><tr><td><table cellpadding="0" cellspacing="10" border="0" id="flickr_badge_wrapper">
<tr><td align='center'>
<?php echo $out ?>
<a href="<?php echo strip_tags( $flickr_home ) ?>">More Photos</a>
</td></tr>
</table>
</td></tr></table>
<!-- End of Flickr Badge -->

		<?php echo $after_widget; ?>
<?php
}

function widget_random_flickr_control() {
	$options = $newoptions = get_option('widget_random_flickr');
	if( $options == false ) {
		$newoptions[ 'title' ] = 'Flickr Photos';
	}
	if ( $_POST["flickr-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["flickr-title"]));
		$newoptions['apikey'] = strip_tags(stripslashes($_POST["flickr-apikey"]));
		$newoptions['userid'] = strip_tags(stripslashes($_POST["flickr-userid"]));
		$newoptions['items'] = strip_tags(stripslashes($_POST["items"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_random_flickr', $options);
	}
	$title  = wp_specialchars($options['title']);
	$items  = wp_specialchars($options['items']);
	$apikey = wp_specialchars($options['apikey']);
	$userid = wp_specialchars($options['userid']);
	$items  = wp_specialchars($options['items']);
	if ( empty($items) || $items < 1 ) $items = 3;

	?>
	<p><label for="flickr-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="flickr-title" name="flickr-title" type="text" value="<?php echo $title; ?>" /></label></p>
	<p><label for="flickr-apikey"><?php _e('Flickr Apikey:'); ?> <input style="width: 250px;" id="flickr-apikey" name="flickr-apikey" type="text" value="<?php echo $apikey; ?>" /></label></p>
	<p><label for="flickr-userid"><?php _e('Flickr UserId:'); ?> <input style="width: 250px;" id="flickr-userid" name="flickr-userid" type="text" value="<?php echo $userid; ?>" /></label></p>
	<p style="text-align:center; line-height: 30px;"><?php _e('How many photos  would you like to display?'); ?> <select id="items" name="items"><?php for ( $i = 1; $i <= 10; ++$i ) echo "<option value='$i' ".($items==$i ? "selected='selected'" : '').">$i</option>"; ?></select></p>
  <p>Leave the Flickr UserId blank to display <a href="http://www.flickr.com/photos/decipher_/">Decipher_'s</a> Flickr photos.</p>
	<input type="hidden" id="flickr-submit" name="flickr-submit" value="1" />
	<?php
}

function flickr_random_widgets_init() {
	register_widget_control('Flickr Random', 'widget_random_flickr_control', 500, 250);
	register_sidebar_widget('Flickr Random', 'widget_random_flickr');
}
add_action( "init", "flickr_random_widgets_init" );

?>
