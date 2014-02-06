<?php


if( $this->joomlaVersion[0] < 3 ) :

?>

<div class="panel" id="wx-geotagger-joomla-panel" style="display:none;">

<h3 class="pane-toggler title"><a href="javascript:void(0);"><span>Geotagger mobile</span></a></h3>

<div id="wx-geotagger-joomla-panel-pane" class="pane-slider content" style="padding-top: 0px; border-top: medium none; padding-bottom: 0px; border-bottom: medium none; overflow: hidden; height: auto;">

<fieldset class="panelform">

<?php 
require_once ( JPATH_PLUGINS.DS.'content'.DS.'weevermaps'.DS.'static'.DS.'views'.DS.'form.view.html.php' ); 
?>

</fieldset>

</div>

</div>

<?php

 else : 

?>

<div id="geotagger" class="tab-pane"><div id="geotagger-inner-hide" style="display:none;">

<?php 
require_once ( JPATH_PLUGINS.DS.'content'.DS.'weevermaps'.DS.'static'.DS.'views'.DS.'form.view.html.php' ); 
?>

</div></div>

<?php

endif;
