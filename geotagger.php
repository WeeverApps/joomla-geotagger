<?php
/*	
*	Geotagger for Joomla
*	(c) 2012-2014 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Authors: 	Robert Gerald Porter <rob@weeverapps.com>
				Matt Grande <matt@weeverapps.com>
				Andrew Holden <andrew@weeverapps.com>
				Aaron Song <aaron@weeverapps.com>
*	Version: 	1.0
*   License: 	GPL v3.0
*
*   This extension is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   This extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details <http://www.gnu.org/licenses/>.
*
*/

defined('_JEXEC') or die;

# Joomla 3.0 nonsense
if( !defined('DS') )
	define( 'DS', DIRECTORY_SEPARATOR );

jimport('joomla.plugin.plugin');

require_once ( JPATH_PLUGINS.DS.'content'.DS.'geotagger'.DS.'static'.DS.'classes'.DS.'common'.'.php' );

class plgContentGeotaggerIntermed extends JPlugin {

	public 		$pluginName 				= "geotagger";
	public 		$pluginNameHumanReadable;
	public  	$pluginVersion 				= "1.0";
	public		$pluginLongVersion 			= "Version 1.0 \"Leif Ericson\"";
	public  	$pluginReleaseDate 			= "February 7, 2014";
	public  	$joomlaVersion;
	public 		$marker_url;
	public 		$default_marker_url;
	public 		$kml_url;
	
	private		$geoData 					= null;
	private		$inputString				= array(
													'longitude' => 0,
													'latitude' 	=> 0,
													'address'	=> null,
													'label'		=> null,
													'marker'	=> null,
													'kml'		=> null
												);
	private		$_com						= "com_content";

	public function __construct( &$subject, $config ) {
		
		$app 			= JFactory::getApplication();
		$option 		= JRequest::getCmd('option');
		$document 		= JFactory::getDocument();
		$root_url 		= substr( JURI::root(), 0, strlen(JURI::root())-1 );
		$post_id 		= null;

		$version 				= new JVersion;
		$this->joomlaVersion 	= substr($version->getShortVersion(), 0, 3);
		
		// kill this when not in correct context
		if( !$app->isAdmin() || $option != "com_content" || ( JRequest::getVar("view") != "article" && !JRequest::getVar("geolocation-pin") ) )
			return false;

		$settings 	= $this->build_settings();
		
		JPlugin::loadLanguage('plg_content_'.$this->pluginName, JPATH_ADMINISTRATOR);
		
		$this->pluginNameHumanReadable = JText::_('GEOTAGGER_PLG_NAME');
		
		if( $id = JRequest::getVar("id") ) {

			$this->getGeoData( $id );

			$post_id = $id;
			//$this->implodeGeoData();

		}

		$this->default_marker_url = $root_url . "/plugins/content/geotagger/assets/images/default-marker.png";

		if( isset($this->geoData[0]) && $this->geoData[0]->marker ) {

			$this->marker_url = $this->geoData[0]->marker;

		} else $this->marker_url = $this->default_marker_url;

		if( isset($this->geoData[0]) && $this->geoData[0]->kml ) {

			$this->kml_url = $this->geoData[0]->kml;

		}

		$jsMetaVar		= "var meta = " . $this->getJsMetaVar();
		
		// if Joomla less than v3
		if( $this->joomlaVersion[0] < 3 ) {

			$document->addScript( "//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" );

			$jsFormInsert	= "

				\$j('#geotagger-joomla-panel').appendTo('#content-sliders-".$post_id."' ); 

				\$j('#geotagger-joomla-panel').show();

			"; 

		} else {

			$jsFormInsert	= "

				\$j('#geotagger').appendTo('#myTabContent' ); 

				\$j('#geotagger-inner-hide').show();

				\$j('<li><a data-toggle=\"tab\" href=\"#geotagger\" id=\"geotagger-tab\">Geotagger</a></li>').appendTo('#myTabTabs');

				\$j('#geotagger-tab').on('click', function(event) {

					console.log( \$geotagger );

					setTimeout( function() {

						google.maps.event.trigger( \$geotagger.map,'resize' );
						\$geotagger.map.setCenter( \$geotagger.center );

					}, 200 );

				});

			";

		}

		$document->addStyleSheet( $root_url . '/plugins/content/geotagger/static/assets/css/style.css', 'text/css', null, array() );


		if( $this->joomlaVersion[0] < 3 ) {

			$document->addStyleSheet( $root_url . '/plugins/content/geotagger/assets/css/joomla.style.css', 'text/css', null, array() );

		} else {

			$document->addStyleSheet( $root_url . '/plugins/content/geotagger/assets/css/joomla.bootstrap.style.css', 'text/css', null, array() );

		}

		require_once ( JPATH_PLUGINS.DS.'content'.DS.'geotagger'.DS.'views'.DS.'joomla.box.view.html.php' );
		require_once ( JPATH_PLUGINS.DS.'content'.DS.'geotagger'.DS.'static'.DS.'js'.DS.'editor.js.php' );
		

		parent::__construct( $subject, $config );
		
	}
	
	
	private function implodeGeoData() {
	
		foreach( (array) $this->geoData as $k=>$v )
		{
		
			$point = array();
			$_ds = ";";
			
			$this->convertToLatLong($v);
			
			$this->inputString['longitude'] 	.= $v->longitude 	. $_ds;
			$this->inputString['latitude'] 		.= $v->latitude 	. $_ds;
			$this->inputString['address'] 		.= $v->address 		. $_ds;
			$this->inputString['label'] 		.= $v->label 		. $_ds;
			$this->inputString['marker'] 		.= $v->marker 		. $_ds;
			$this->inputString['kml'] 			.= $v->kml 			. $_ds;
		
		}
	
	}
	
	
	private function convertToLatLong( &$obj ) {
	
		$point = rtrim( ltrim( $obj->location, "(POINT" ), ")" );
		$point = explode(" ", $point);
		$obj->latitude = $point[0];
		$obj->longitude = $point[1];
	
	}
	
	
	private function getGeoData( $id ) {
	
		$db = JFactory::getDBO();
		
		$query = "SELECT component_id, AsText(location) AS location, address, label, kml, marker ".
				"FROM
					#__weever_maps ".
				"WHERE
					component = ".$db->quote($this->_com)." 
					AND
					component_id = ".$db->quote($id);
					
		$db->setQuery($query);
		$this->geoData = $db->loadObjectList();

		if( !$this->geoData )
			return;

		foreach( (array) $this->geoData as $k=>$v ) {

			$this->convertToLatLong( $v );

		}
	
	}

	public function onContentAfterSaveIntermed( $context, $data, $isNew ) {
	
		$_ds = ";";			
		
		$geoLatArray = 		explode( 	$_ds, rtrim( JRequest::getVar("geolocation-latitude"), 		$_ds) 	);
		$geoLongArray = 	explode( 	$_ds, rtrim( JRequest::getVar("geolocation-longitude"), 	$_ds) 	);
		$geoAddressArray = 	explode( 	$_ds, rtrim( JRequest::getVar("geolocation-address"), 		$_ds) 	);
		$geoLabelArray = 	explode( 	$_ds, rtrim( JRequest::getVar("geolocation-label"), 		$_ds) 	);
		$geoMarkerArray = 	explode( 	$_ds, rtrim( JRequest::getVar("geolocation-pin"), 		$_ds) 	);
		
		$db = JFactory::getDBO();

		$query = " 	DELETE FROM #__weever_maps 
					WHERE
						component_id = ".$db->quote($data->id)."
						AND
						component = ".$db->quote($this->_com);
						
	
		$db->setQuery($query);
		$db->query();

		if( JRequest::getVar("geolocation-on") == 0 )
			return;

		if($kml = rtrim( JRequest::getVar("geolocation-url"), $_ds) )	{
			
			$query = " 	INSERT  ".
					"	INTO	#__weever_maps ".
					"	(component_id, component, kml) ".
					"	VALUES ('".$data->id."', ".$db->quote($this->_com).", ".$db->quote($kml).")";
			
			$db->setQuery($query);
			$db->query();
			
		}

		if( ( $geoLatArray[0] == 0 && $geoLongArray[0] == 0 ) )
			return; 
		
		foreach( (array) $geoLatArray as $k=>$v ) {
		
			if( !empty($v)) {
			
				$query = " 	INSERT  ".
						"	INTO	#__weever_maps ".
						"	(component_id, component, location, address, label, marker) ".
						"	VALUES ('".$data->id."', ".$db->quote($this->_com).", 
								GeomFromText(' POINT(".$geoLatArray[$k]." ".$geoLongArray[$k].") '),
								".$db->quote($geoAddressArray[$k]).", 
								".$db->quote($geoLabelArray[$k]).", 
								".$db->quote($geoMarkerArray[$k]).")";
							
			
				$db->setQuery($query);
				$db->query();
			
			}
		
		}
		
		
	}

	private function build_settings() {

		$settings 	= new GeolocationSettings();
		$settings->map_width       = 450;
		$settings->map_height      = 200;
		$settings->default_zoom    = 16;
		//$settings->map_position    = get_option('geolocation_map_position');
		// Do we want to display the pin?
		$settings->show_custom_pin = 0;
		$settings->pin_url         = "http://weeverapp.com/media/sprites/default-marker.png";
		$settings->pin_shadow_url  = "";

		return $settings;
		//$settings->pin_shadow_url  = plugins_url('img/wp_pin_shadow.png', __FILE__ );
		//$settings->zoom_url        = esc_js(plugins_url('img/zoom/', __FILE__));

		/*if ( $this->settings === null ) {
			if(get_option('geolocation_map_width') == '0')
				update_option('geolocation_map_width', '450');
				
			if(get_option('geolocation_map_height') == '0')
				update_option('geolocation_map_height', '200');
				
			if(get_option('geolocation_default_zoom') == '0')
				update_option('geolocation_default_zoom', '16');
				
			if(get_option('geolocation_map_position') == '0')
				update_option('geolocation_map_position', 'after');

			$settings = new GeolocationSettings();
			$settings->map_width       = (int) get_option('geolocation_map_width');
			$settings->map_height      = (int) get_option('geolocation_map_height');
			$settings->default_zoom    = (int) get_option('geolocation_default_zoom');
			$settings->map_position    = get_option('geolocation_map_position');
			// Do we want to display the pin?
			$settings->show_custom_pin = get_option('geolocation_wp_pin');
			$settings->pin_url         = esc_js(esc_url(plugins_url('img/wp_pin.png', __FILE__ )));
			$settings->pin_shadow_url  = plugins_url('img/wp_pin_shadow.png', __FILE__ );
			$settings->zoom_url        = esc_js(plugins_url('img/zoom/', __FILE__));
		}

		return $settings;*/
	}

	private function getJsMetaVar() {

		$meta = new stdClass;

		$meta->geo 		= $this->geoData;
		$meta->on 		= 1;
		$meta->public 	= 1;

		return json_encode($meta);

	}

	
} 



/* Stupid trick to make this work in J3.0 and J2.5 */

if (version_compare ( JVERSION, '3.0', '<' )) {
  class plgContentGeotagger extends plgContentGeotaggerIntermed {
   public function onContentAfterSave($context, &$article, $isNew) {
   $this->onContentAfterSaveIntermed ( $context, $article, $isNew );
  }
}
} else {
  class plgContentGeotagger extends plgContentGeotaggerIntermed {
   public function onContentAfterSave($context, $article, $isNew) {
   $this->onContentAfterSaveIntermed ( $context, $article, $isNew );
  }
}
}

