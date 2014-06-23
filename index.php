<?php
/*
Plugin Name: Childpage Navi Plugin
Plugin URI: http://www.pixelbar.be
Description: Das Pixelbar Plugin erweitert das Wordpress um einige sehr häufig genutzte Funktionen wie z.B. PHP-Textwidgets, Childnaviausgaben etc.
Version: 0.1
Author: Adrian Lambertz
Author URI: http://www.pixelbar.be

GitHub Plugin URI: https://github.com/PixelbarEupen/Childpage-Navi
GitHub Access Token: 6ca583973da0e33ee1a6c90c3e4920e6143369ca
*/


//Define Plugin Path for Widgets etc.
define(PLUGIN_PATH,plugins_url().'/Childpage-Navi/');


/**********************/
/* CHILDPAGE FUNCTION */
/**********************/

// Set CSS und JS function
function css(){
	wp_register_style('widget',PLUGIN_PATH.'css/widgets.css');
}
function js(){
	wp_register_script('widget',PLUGIN_PATH.'js/widgets.js');	
}
add_action('init','css',100);
add_action('init','js',100);


// DIE TIEFE EINES ELEMENTS RAUSFINDEN (BENÖTIGT FÜR CHILDNAVI)
function get_depth($postid) {
  $depth = ($postid == get_option('page_on_front')) ? -1 : 0;
  while ($postid > 0) {
    $postid = get_post_ancestors($postid);
    $postid = $postid[0];
    $depth++;
  }
  return $depth;
}
// CHILDNAVI FUNCTION
function childnavi($debug, $tiefe,$css,$js) {
    
     global $post;
     $parent_id = $post->post_parent;
	 
	 if('post' == get_post_type()){
	 		$post_categories = wp_get_post_categories( get_the_ID() );
	 		$last_cat = end($post_categories);
	 		//print_r($last_cat);
	 		if($last_cat == 1){ // 1 IST ALLGEMEIN
	 			$page_id = 20; //ID 20 ist NEWS
	 		} elseif($last_cat == 96){ //96 ist Kategorie EUROJUKA
		 		$page_id = 69; //ID 69 ist Seite EUROJUKA (für activestate)
	 		}
	 } elseif('termine' == get_post_type()) {
	 		$page_id = 21; //21 is termine	
	 } elseif('partner' == get_post_type()) {
		  	$page_id = 71; //71 is VORTEILE
	 } else {	 
		 $page_id = $post->ID;
	 }
	 
      $depth = get_depth($page_id);
      $ancestors = get_post_ancestors($page_id);
     
           $debuging = 'PAGE ID: '.$page_id.'<br />';
           $debuging .= 'DEPTH: '.$depth.'<br />';
           $ancestor = '';
           $i = 1;
           foreach($ancestors as $asc):
           	
           	$ancestor = $asc;
           	if($i > 1){ $ancestor .= ',';}
           	
           	$i++;
           	
           endforeach;
           $debuging .= 'ANCESTORS: '.$ancestor.'<br />';
           //$debuging .= 'KINDERELEMENTE: '.count($children);

      if($depth > 1) {
           $page_id = end($ancestors);

      }
      $activestate = '';
     
      $args = array(
          'depth'        => $tiefe,
          'show_date'    => '',
          'date_format'  => get_option('date_format'),
          'child_of'     => $page_id,
          'exclude'      => '',
          'include'      => '',
          'title_li'     => '',
          'echo'         => 0,
          'authors'      => '',
          'sort_column'  => 'menu_order',
          'sort_order'   => 'ASC',
          'link_before'  => '',
          'link_after'   => '<span class="icon-arrow"></span>',
          'walker'       => '',
          'post_type'    => 'page',
         'post_status'  => 'publish'
     );
    
     if(wp_list_pages($args) != ''){
          $output = '<ul class="childnav">';
          $output .= wp_list_pages($args);
          $output .= '</ul>';
     }
    
	 if($debug == 'true')
	 	$output = $debuging.$output;
     
     if($css == 'true')
	 wp_enqueue_style('widget');
	 
	 if($js == 'true')	
	 wp_enqueue_script('widget');
	 
     return $output;

}

/********************/
/* CHILDPAGE WIDGET */
/********************/


class childpagewidget extends WP_Widget
{
  function childpagewidget()
  {
    $widget_ops = array(
    	'classname' => 'childpagewidget',
    	'description' => 'Gibt die Navigation anhand von Seiten und Elternseiten aus.' 
    );
    $this->WP_Widget(
    	'childpagewidget',
    	'Eltern/Kind Seitennavigation',
    	 $widget_ops
    );
  }
 
  function form($instance)
  {
    $instance = wp_parse_args(
    	(array) $instance,
    	 array( 
    		'debug' => '',
    		'depth' => '',
    		'css' => '',
    		'js' => ''
    	)
    );
    $debug = $instance['debug'];
    $depth = $instance['depth'];
    $css = $instance['css'];
    $js = $instance['js'];
?>
  <p>
  	<table>
  		<tr>
	  		<td><label for="<?php echo $this->get_field_id('debug'); ?>">Debugging: </label></td>
	  		<td><input class="widefat" id="<?php echo $this->get_field_id('debug'); ?>" name="<?php echo $this->get_field_name('debug'); ?>" type="checkbox" value="true" <?php if($debug == 'true'){echo "checked";} ?>/></td>
	  	</tr>
	  	<tr>
	  		<td colspan="2"><hr /></td>
	  	</tr>
	  	<tr>
	  		<td>
	  			<label for="<?php echo $this->get_field_id('depth'); ?>">Anzuzeigende Tiefe: </label>
	  		</td>
	  		<td>

	  			<select class="widefat" style="width: 40px;" id="<?php echo $this->get_field_id('depth'); ?>" name="<?php echo $this->get_field_name('depth'); ?>">
	  			<?php $values = array('1','2','3');
	  				foreach($values as $value):?>
	  					<option value="<?php echo $value; ?>" <?php if($depth == $value){echo "selected";} ?>><?php echo $value; ?></option>
	  				<?php endforeach; ?>

	  			</select>
	  			
	  		</td>
	  	</tr>
	  	<tr>
		  	<td colspan="2"><p class="description">Wähle hier die maximale Tiefe aus, die angezeigt werden soll.</p></td>
	  	</tr>
	  	<tr>
	  		<td colspan="2"><hr /></td>
	  	</tr>
	  	<tr>
	  		<td><label for="<?php echo $this->get_field_id('css'); ?>">Nutze basic CSS: </label></td>
	  		<td><input class="widefat" id="<?php echo $this->get_field_id('css'); ?>" name="<?php echo $this->get_field_name('css'); ?>" type="checkbox" value="true" <?php if($css == 'true'){echo "checked";} ?>/></td>
	  	</tr>
	  	<tr>
	  		<td><label for="<?php echo $this->get_field_id('js'); ?>">Nutze jQuery Animation: </label></td>
	  		<td><input class="widefat" id="<?php echo $this->get_field_id('js'); ?>" name="<?php echo $this->get_field_name('js'); ?>" type="checkbox" value="true" <?php if($js == 'true'){echo "checked";} ?>/></td>
	  	</tr>
	  	
  	</table>
  	
  </p>
  
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['debug'] = $new_instance['debug'];
    $instance['depth'] = $new_instance['depth'];
    $instance['css'] = $new_instance['css'];
    $instance['js'] = $new_instance['js'];
    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $debug = empty($instance['debug']) ? ' ' : apply_filters('widget_debug', $instance['debug']);
    $depth = empty($instance['depth']) ? ' ' : apply_filters('widget_depth', $instance['depth']);
	$css = empty($instance['css']) ? ' ' : apply_filters('widget_css', $instance['css']);
	$js = empty($instance['js']) ? ' ' : apply_filters('widget_css', $instance['js']);
	echo childnavi($debug, $depth, $css, $js);
   
 
    echo $after_widget;
  }
 
}
add_action( 'widgets_init', create_function('', 'return register_widget("childpagewidget");') );
?>