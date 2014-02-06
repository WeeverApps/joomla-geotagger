<?php
/*	global $settings, $post;
	if ( function_exists( CMS . '_before_geolocation_form' ) ) {
		call_user_func( CMS . '_before_geolocation_form' );
	}*/

	$url = "http://weeverapp.com/media/sprites/default-marker.png";

?>
<div id="wx-geotagger-form">

 <!-- start: geotagger interface -->

	<label class="screen-reader-text" for="geolocation-address">Geotagger</label>

	<label>Street address or map coordinates</label>

	<input type="text" id="geolocation-address" name="geolocation-address" autocomplete="off" value="" />

	<input id="geolocation-load" type="button" class="button geolocationadd" value="Set" />

	<input type="hidden" id="geolocation-latitude" name="geolocation-latitude" />
	<input type="hidden" id="geolocation-longitude" name="geolocation-longitude" />
    <br>
    <br>

	<div class="embed-container" style="border:1px solid #c6c6c6; position: relative; padding-bottom: 62.5%; height: 0; overflow: hidden; max-width: 100%; height: auto;">

        <div id="geolocation-map" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></div>

    </div>

    <br>
    <!-- Optional: Print Selected Coordinates -->
    <!-- <div class="latlng">Coordinates: <span id="latlng"></span></div> -->

    <!-- Map markers and KML addresses -->

	<div id="pin-settings" class="geotagger-info">

		<div>Map marker image address</div>
		<?php
		/* TODO: Replace with x-platform code */
		//$url = get_post_meta($post->ID, 'weever_map_marker', true);
		//if ( !$url and $settings->show_custom_pin ) {
		//	$url = $settings->pin_url;
		//}
		?>
		<input type="text" id="geolocation-pin" name="geolocation-pin" placeholder="http://site.com/marker.png" value="<?php echo $url; ?>" />
	</div>
	<br>

	<div id="kml-settings" class="geotagger-info">
		<div>KML file address (advanced)</div>
		<input type="text" id="geolocation-url" name="geolocation-url" placeholder="http://site.com/file.kml" value="<?php /*echo get_post_meta($post->ID, 'weever_kml', true); TODO: Cross-platform method for this.*/ ?>" />
	</div>

	<br>

	<div id="geotagger-on-off" class="geotagger-info">

		<label>Geotag a location for this item</label>
		<div>
			<input id="geolocation-enabled" name="geolocation-on" type="radio" value="1" />
			<label for="geolocation-enabled">Yes</label>
		&nbsp;
			<input id="geolocation-disabled" name="geolocation-on" type="radio" value="0" />
			<label for="geolocation-disabled">No</label>
		</div>

	</div>

	<br>

	<div id="geotagger-about" class="geotagger-info">Geotagger allows you to associate a content item with a specific location. <!--a href="#">Settings</a--></div>

	<br>

</div>