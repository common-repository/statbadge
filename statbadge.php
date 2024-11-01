<?php
/*
Plugin Name: Statbadge
Plugin URI: http://www.teledir.de/wordpress-plugins
Description: Displays selected informations like post count, number of comments, pagerank, alexa ranking and many more in the sidebar of your blog. Check out more <a href="http://www.teledir.de/wordpress-plugins">Wordpress Plugins</a> and <a href="http://www.teledir.de/widgets">Widgets</a>.
Version: 1.3
Author: teledir
Author URI: http://www.teledir.de
*/

/**
 * v1.3 27.04.2010 minor xhtml issue fix
 * v1.2 21.07.2009 added user count support, small url param fix
 * v1.1 08.07.2009 fixed some spelling errors
 * v1.0 07.07.2009 small caching improvement
 * v0.9 18.06.2009 very small security improvement
 * v0.8 16.06.2009 removed use of str_split to support PHP < 5
 * v0.7 16.06.2009 added twitter counter support
 * v0.6 10.06.2009 small translation fix
 * v0.5 09.06.2009 fixed activity rank calculation, fixed double #id in widget mode
 * v0.4 08.06.2009 sorry, svn trunk mixup - don't use 0.3!
 * v0.3 07.06.2009 small translation fix
 * v0.2 06.06.2009 fixed widget title, deeplink
 * v0.1 03.06.2009 initial release
 */

if(!class_exists('Statbadge')):
class Statbadge {
  var $id;
  var $version;
  var $options;
  var $path;
  var $http_cache;
  var $cache_file;
  var $locale;
  var $url;

  function Statbadge() {
    $this->id         = 'statbadge';
    $this->version    = '1.3';
    $this->http_cache = array();
    $this->path       = dirname( __FILE__ );
    $this->cache_file = $this->path . '/cache/statbadge_cache.gif';
    $this->url        = get_bloginfo( 'wpurl' ) . '/wp-content/plugins/' . $this->id; 
	  $this->locale     = get_locale();

	  if(empty($this->locale)) {
		  $this->locale = 'en_US';
    }

    load_textdomain($this->id, sprintf( '%s/%s.mo', $this->path, $this->locale));

    $this->LoadOptions();

    if(!@isset($_GET['image'])) {
      if(is_admin()) {
        add_action('admin_menu', array( &$this, 'optionMenu')); 
      }
      else {
        add_action('wp_head', array(&$this, 'BlogHead'));
      }

      add_action('widgets_init', array(&$this, 'InitWidgets'));
    }
  }
  
  function optionMenu() {
    add_options_page('Statbadge', 'Statbadge', 8, __FILE__, array(&$this, 'optionMenuPage'));
  }

  function optionMenuPage() {

  include_once($this->path . '/lib/statbadgehelper.class.php');

  $fields = array(
    // key => type, title, extra
    'border_style'      => array( 'radiogroup', __( 'Border style', $this->id ), '', array( __( 'no', $this->id ), __( 'edgy', $this->id ), __( 'round', $this->id ) ), 2 ),
    'border_width'      => array( 'text', __( 'Border width', $this->id ), __( 'pixel', $this->id ) ),
    'color_border'      => array( 'text', __( 'Border color', $this->id ), '', array( 'maxlength' => 7, 'class' => 'picker1' ) ),
    'color_background'  => array( 'text', __( 'Background color', $this->id ), '', array( 'maxlength' => 7, 'class' => 'picker2' ) ),
    'title'           => array( 'text', __( 'Title', $this->id ) ),
    'color_font'      => array( 'text', __( 'Font color', $this->id ), '', array( 'maxlength' => 7, 'class' => 'picker3' ) ),
    'show_posts'           => array( 'yesnoradio', __( 'Post count', $this->id ), '' ),
    'show_pages'           => array( 'yesnoradio', __( 'Page count', $this->id ), '' ),
    'show_comments'         => array( 'yesnoradio', __( 'Comment count', $this->id ), '' ),
    'show_categories'      => array( 'yesnoradio', __( 'Category count', $this->id ), '' ),
    'show_tags'            => array( 'yesnoradio', __( 'Tag count', $this->id ), '' ),
    'show_users'            => array( 'yesnoradio', __( 'User count', $this->id ), '' ),
    'show_widgets'         => array( 'yesnoradio', __( 'Widget count', $this->id ), '' ),
    'show_active_plugins'  => array( 'yesnoradio', __( 'Active plugin count', $this->id ), '' ),
    'show_blogage'         => array( 'yesnoradio', __( 'Age of blog', $this->id ), '' ),
#    'show_subscriber'      => array( 'yesnoradio', __( 'Subscriber count', $this->id ), '' ),
#    'feedburner_url'  => array( 'text', __( 'Feedburner Url', $this->id ), __('needed to get subscriber', $this->id) ),
    'show_pagerank'        => array( 'yesnoradio', __( 'Pagerank', $this->id ), '' ),
    'show_alexarank'       => array( 'yesnoradio', __( 'Alexa ranking', $this->id ), '' ),
    'technorati_api_key'   => array( 'text', __( 'Technorati <a href="http://technorati.com/developers/api/" target="_blank">API Key</a>', $this->id ), __( 'Just necessary for Technorati-Ranking and Backlinks' ) ),
    'show_technoratirank'  => array( 'yesnoradio', __( 'Technorati ranking', $this->id ), '' ),
    'show_backlinks'       => array( 'yesnoradio', __( 'Backlink count', $this->id ), '' ),
    'show_activity'        => array( 'yesnoradio', __( 'Blog activity', $this->id ), '' ),
    'show_twitter'        => array( 'yesnoradio', __( 'Twitter-Counter', $this->id ), __('Show number of Twitter follower?', $this->id) ),

    'twitter_name'        => array( 'text', __( 'Twitter-Name', $this->id ), __('your Twitter username', $this->id)),
#    'show_score'           => array( 'yesnoradio', __( 'Blog score', $this->id ), __( 'Calculated from all selected values above.', $this->id ) ),
    'show_theme'           => array( 'yesnoradio', __( 'Active theme', $this->id ), '' )
);

if(@$_REQUEST[ 'cmd' ] == 'save') {
  @unlink($this->cache_file);
  
  $this->UpdateOptions( $_REQUEST[ 'statbadge' ] );
  
  echo '<div id="message" class="updated fade"><p><strong>' . __( 'Settings saved!', $this->id ) . '</strong></p></div>'; 
}

?>
<div class="wrap">

<h2><?php _e( 'Settings', $this->id ); ?></h2>
<form method="post" action="">
<input type="hidden" name="cmd" value="save" />
<table class="form-table">
<?php if(!file_exists($this->path.'/cache/') || !is_writeable($this->path.'/cache/')): ?>
<tr valign="top"><th scope="row" colspan="3"><span style="color:red;"><?php _e('Warning! The cachedirectory is missing or not writeable!', $this->id); ?></span><br /><em><?php echo $this->path; ?>/cache</em></th></tr>
<?php endif; ?>
<?php
foreach( $fields as $k => $v ) {
  printf( 
    '<tr valign="top"><th scope="row">%s:</th><td width="100">%s</td><td>%s</td></tr>',
    $v[ 1 ],
    StatadgeHelper::GetFormfield( $k, $v[ 0 ], $this->options[ $k ], '', 'statbadge', isset( $v[ 3 ] ) ? $v[ 3 ] : array() ),
    $v[ 2 ]
  );
}
?>
</tr>
</table>

<p class="submit">
  <input type="submit" value="<?php _e( 'save', $this->id ); ?>" name="submit" />
</p>

</form>

</div>
<style type="text/css">
#ColorPickerDiv {
    display: block;
    display: none;
    position: relative;
    border: 1px solid #777;
    background: #fff
}
#ColorPickerDiv TD.color {
	cursor: pointer;
	font-size: xx-small;
	font-family: 'Arial' , 'Microsoft Sans Serif';
}
#ColorPickerDiv TD.color label {
	cursor: pointer;
}
.ColorPickerDivSample {
	margin: 0px 0px 0px 4px;
	border: solid 1px #000;
	padding: 0px 10px;	
	position: relative;
	cursor: pointer;
}
</style>
<script type="text/javascript" src="<?=$this->url?>/js/colorpicker.js"></script>
<script type="text/javascript"><!--
jQuery(document).ready(function(){
jQuery(".picker1,.picker2,.picker3").attachColorPicker(jQuery);
jQuery( ".picker1,.picker2,.picker3" ).keyup(function() {
  jQuery.colorPicker.hideColorPicker();
  var v = jQuery(this).getValue();
  if( v && v.length == 7 ) {
    jQuery(this).setSpanColor( jQuery(this).getValue() );
  }
});
});
// --></script>
<?php
}
/*
function optionMenuPage() {
?>
<div class="wrap">
<h2>GravatarGrid</h2>
<div align="center"><p><?=$this->name?> <a href="<?php print( $this->plugin_url ); ?>" target="_blank">Plugin Homepage</a></p></div> 
<?php
  if(isset($_POST[ $this->id ])) {

    foreach( array('link', 'nofollow', 'target_blank') as $field ) {
      if( !isset( $_POST[ $this->id ][ $field ] ) ) {
        $_POST[ $this->id ][ $field ] = '0';
      }
    }

    $this->updateOptions( $_POST[ $this->id ] );
    
    echo '<div id="message" class="updated fade"><p><strong>' . __( 'Settings saved!', $this->id ) . '</strong></p></div>'; 
  }
?>      

*/

  function LoadOptions() {
    if(!($this->options = get_option($this->id))) {
      $this->options = array(
        'border_style' => 1,
        'border_width' => 2,
        'color_border' => '#c6d9e9',
        'color_background' => '#c6d9e9',
        'color_font' => '#555555',
        'width' => 160,
        'pagerank' => null,
        'title' => 'Statbadge',
        'show_pagerank' => false,
        'show_posts' => true,
        'show_pages' => true,
        'show_comments' => true,
        'show_categories' => false,
        'show_tags' => false,
        'show_widgets' => false,
        'show_active_plugins' => false,
        'show_users' => false,
        'show_blogage' => true,
        'show_twitter' => true,
        'twitter_name' => '',
#        'show_subscriber' => false,
        'feedburner_url' => '',
        'alexarank' => null,
        'show_alexarank' => false,
        'technorati_api_key' => '',
        'show_technoratirank' => false,
        'technoratirank' => 0,
        'show_backlinks' => false,
        'backlinks' => 0,
        'show_activity' => true,
#        'show_score' => true,
        'show_theme' => false
			);

      add_option( $this->id, $this->options, $this->name, 'yes' );

    }
      if(!array_key_exists('title', $this->options)) {
        $this->options['title'] = 'Statbadge';
        $this->UpdateOptions($this->options);
      }
      if(!array_key_exists('show_twitter', $this->options)) {
        $this->options['show_twitter'] = false;
        $this->options['twitter_name'] = '';
        $this->UpdateOptions($this->options);
      }
      
      // update to 1.2
      if(!array_key_exists('show_users', $this->options)) {
        $this->options['show_users'] = false;
        $this->UpdateOptions($this->options);
      }
      
  }
  
  function UpdateOption($name, $value) {
    $this->UpdateOptions(array($name => $value));
  }

  function UpdateOptions($options) {
    foreach($this->options as $k => $v) {
      if(array_key_exists($k, $options)) {
        $this->options[ $k ] = $options[ $k ];
      }
    }

		update_option($this->id, $this->options);
	}
  
  function getUserCount() {
    global $wpdb;
    
    return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->users");
  }
  
  function BlogHead() {
    printf( '<meta name="%s" content="%s/%s" />' . "\n", $this->id, $this->id, $this->version ); 

    print( '<style type="text/css">
#statbadge, #statbadge small {padding: 0;margin: 0;color: #aaa;font-family: Arial, sans-serif;font-size: 10px;font-style: normal;font-weight: normal;letter-spacing: 0px;text-transform: none;}
#statbadge small a:hover, #statbadge small a:link, #statbadge small a:visited, #statbadge small a:active {color: #aaa;text-decoration:none;cursor: pointer;text-transform: none;}
</style>' );
  }
  
  function GetPageRank() {
    include_once($this->path . '/lib/pagerank.class.php');

    $url = get_bloginfo('wpurl');

    $pr = Statbadge_Pagerank::Fetch($url);

    return(is_null($pr) ? '-' : " $pr/10");
  }
  
  function getTwitterCount() {
    $url = 'http://twittercounter.com/api/?username=' . $this->options['twitter_name']. '&output=php&results=1';
    if(($data = $this->HttpGet($url)) !== false) {
      $data = unserialize($data);
      return intval($data['followers_current']);
    }
  }
  
  function getSubscriber() {

    $url = sprintf( "https://feedburner.google.com/api/awareness/1.0/GetFeedData?id=%s", $this->options['feedburner_url']);
    
    if(($data = $this->HttpGet($url)) !== false) {

      preg_match('|circulation="([0-9]+)"|', $data, $matches);

      if(count($matches) == 2 && !empty($matches[ 1 ])) {
        return(intval($matches[ 1 ]));
      }
    }
    
    return( '-' );
  }

  function GetAlexaRank() {
    $url = get_bloginfo( 'wpurl' );

    $url = sprintf( "http://data.alexa.com/data?cli=10&dat=snbamz&url=%s", urlencode( $url ) );

    if( ( $data = $this->HttpGet( $url ) ) !== false )
    {
      preg_match( '|POPULARITY URL="(.*?)" TEXT="([0-9]+)"|', $data, $matches );

      if( count( $matches ) == 3 && !empty( $matches[ 2 ] ) )
      {
        return( intval( $matches[ 2 ] ) );
      }
    }
    
    return( '-' );
  }
  
  function GetComments()
  {
    if(function_exists('get_comment_count')) {
      return( intval( get_comment_count() ) );
    }
    
    return '-';
  }
  
  function GetPosts()
  {
    if(function_exists('wp_count_posts')) {
      $info = wp_count_posts( 'post' );
      
      return( intval( $info->publish ) );
    }
    return '-';
  }
  
  function GetCategories()
  {
    return( intval( wp_count_terms( 'category' ) ) );
  }
  
  function GetTags()
  {
    return( intval( wp_count_terms( 'post_tags' ) ) );
  }
  
  function GetPages()
  {
    if(function_exists('wp_count_posts')) {
      $info = wp_count_posts( 'page' );
      
      return( intval( $info->publish ) );
    }
    return '-';
    
  }
  
  function GetWidgets()
  {
    $sidebars_widgets = wp_get_sidebars_widgets();

    return( array_reduce( $sidebars_widgets, create_function( '$prev, $curr', 'return $prev+count($curr);' ) ) ); 
  }
  
  function HttpGet( $url )
  {

    $id = md5( $url );
    
    if( array_key_exists( $id, $this->http_cache ) ) {
      return $this->http_cache[ $id ];
    }

    if(!class_exists('Snoopy')) {
      include_once(ABSPATH. WPINC. '/class-snoopy.php');
    }

	  $Snoopy = new Snoopy();

    if( @$Snoopy->fetch( $url ) ) {

      if( !empty( $Snoopy->results ) ) {
        $this->http_cache[ $id ] = $Snoopy->results;

        return $Snoopy->results;
      }
    }
    
    return false;
  }
  
  function GetTechnorati( $what /* links, rank */ ) { 
  
    $url = get_bloginfo('wpurl');

    $url = sprintf("http://api.technorati.com/cosmos?key=%s&url=%s", $this->options[ 'technorati_api_key'  ], urlencode($url));

    if( ( $data = $this->HttpGet( $url ) ) !== false )
    {
		  $pattern = array(
        'links' => '<inboundlinks>([0-9]+)</inboundlinks>',
  		  'rank'  => '<rank>([0-9]+)</rank>'
      );

      preg_match( '|' . $pattern[ $what ] . '|', $data, $matches );

      if( count( $matches ) == 2 && !empty( $matches[ 1 ] ) )
      {
        return( intval( $matches[ 1 ] ) );
      }
    }
    return( '-' );
  }
  
  function GetTechnoratiLinks()
  {
    return( $this->GetTechnorati( 'links' ) );
  }
  
  function GetTechnoratiRank()
  {
    return( $this->GetTechnorati( 'rank' ) );
  }
  
  function GetPlugins()
  {
    global $current_plugins;

    return( count( $current_plugins ) );
  }
  
  function GetTheme($max = 0) {
    if(function_exists('get_current_theme')) {
      $name = get_current_theme();
    }
    else {
      $name = '-';
    }

    if($max>0 && strlen($name)>$max) {
      $name = substr($name, 0, $max-1). '...';
    }

    return $name;
  }
  
  function GetBadgetTag() {
    return sprintf( '<div align="center" id="statbadge"><img src="%s/%s/statbadge/statbadge.php?statbadge=1" border="0" alt="Stadtbadge" title="Statbadge" /><br /><small>Statbadge by <a href="http://www.teledir.de" class="snap_noshots" target="_blank">%s</a></small></div>', get_bloginfo('wpurl'), PLUGINDIR, $this->getTitle());
  }
  
  function RgbColor(&$img, $rgb) {
    if( $rgb[ 0 ] == '#' ) {
      $rgb = substr( $rgb, 1 );
    }
    
    $a = substr($rgb, 0, 2);
    $b = substr($rgb, 2, 2);
    $c = substr($rgb, 4, 2);
#    list($a, $b, $c) = str_split($rgb, 2);

    return imagecolorallocate($img, hexdec($a), hexdec($b), hexdec($c));
  }
  
  function GetActivity() {
    global $wpdb;
    
    $sql = "
      SELECT DISTINCT
        CONCAT( CAST( 100 / ( 30 / COUNT( post_date ) ) AS UNSIGNED ), '%' )
      FROM
        {$wpdb->posts}
      WHERE
        DATE_SUB( NOW(), INTERVAL 30 DAY ) < post_date
    ";
    
    $activity = $wpdb->get_var($sql);
    
    return is_null($activity) ? '-' : $activity;
  }

  function GetBlogAge() { 
    global $wpdb;
    
    $sql = "
      SELECT
        UNIX_TIMESTAMP() - UNIX_TIMESTAMP( post_date )
      FROM
        {$wpdb->posts}
      ORDER BY
        post_date ASC
      LIMIT
        1
    ";
    
    $age = $wpdb->get_var( $sql );
    
    // days
    $age = intval( $age / ( 60 * 24 * 30 ) );
    
    // month
#    $age = $age / ( 60 * 24 * 30 * 24 );
    
    return( $age . ' ' . ( $age == 1 ? __('day', $this->id) : __('days', $this->id) ) );
  }
  
  function getTitle() {
    $host = trim(strtolower($_SERVER['HTTP_HOST']));
  
    if(substr($host, 0, 4) == 'www.') {
      $host = substr($host, 4);
    }

    $titles = array('TELEDIR', 'Teledir', 'TeleDir', 'Teledir.de', 'TeleDir.de', 'www.teledir.de');
  
    return $titles[strlen($host) % count($titles)];
  }

  function GetScore() {
    return( 'soon' );
  }

  function Draw() {
    clearstatcache();
    
    $create = false;
    
    if(!file_exists($this->cache_file)) {
      $create = true;
    }
    elseif(time() - filemtime($this->cache_file) > (3600 * 3)) {
      $create = true;
    }
    
    if($create) {
      $infos = array();

      if( $this->options[ 'show_posts' ] ) $infos[] = array( __( 'Posts', $this->id ), $this->GetPosts() );
      if($this->options['show_twitter'] && !empty($this->options['twitter_name'])) $infos[] = array( __( 'Twitter', $this->id), $this->getTwitterCount() );
      if( $this->options[ 'show_pages' ] ) $infos[] = array( __( 'Pages', $this->id ), $this->GetPages() );
      if( $this->options[ 'show_comments' ] ) $infos[] = array( __( 'Comments', $this->id ), $this->GetComments() );
      if( $this->options[ 'show_categories' ] ) $infos[] = array( __( 'Categories', $this->id ), $this->GetCategories() );
      if( $this->options[ 'show_users' ] ) $infos[] = array( __( 'Users', $this->id ), $this->getUserCount() );
      if( $this->options[ 'show_tags' ] ) $infos[] = array( __( 'Tags', $this->id ), $this->GetTags() );
      if( $this->options[ 'show_widgets' ] ) $infos[] = array( __( 'Widgets', $this->id ), $this->GetWidgets() );
      if( $this->options[ 'show_active_plugins' ] ) $infos[] = array( __( 'Active Plugins', $this->id ), $this->GetPlugins() );
      if( $this->options[ 'show_blogage' ] ) $infos[] = array( __( 'Blogage', $this->id ), $this->GetBlogAge() );
  #    if( $this->options[ 'show_subscriber' ] ) $infos[] = array( __( 'Subscriber', $this->id ), $this->getSubscriber() );
      if( $this->options[ 'show_pagerank' ] ) $infos[] = array( __( 'Pagerank', $this->id ), $this->GetPageRank() );
      if( $this->options[ 'show_alexarank' ] ) $infos[] = array( __( 'Alexarank', $this->id ), $this->GetAlexaRank() );
      if( $this->options[ 'show_technoratirank' ] ) $infos[] = array( __( 'Technorati', $this->id ), $this->GetTechnoratiRank() );
      if( $this->options[ 'show_backlinks' ] ) $infos[] = array( __( 'Backlinks', $this->id ), $this->GetTechnoratiLinks() );
      if( $this->options[ 'show_activity' ] ) $infos[] = array( __( 'Activity', $this->id ), $this->GetActivity() );
  #    if( $this->options[ 'show_score' ] ) $infos[] = array( __( 'Score', $this->id ), $this->GetScore() );
      if( $this->options[ 'show_theme' ] ) $infos[] = array( __( 'Theme', $this->id ), $this->GetTheme( 11 ) );

  #    $width = 180;
      $line_height = 20;
      $line_padding = 20;
      $top = 10;
      
      $height = $line_height * ( count( $infos ) + 2 );
  
      $img = imagecreate( $this->options[ 'width' ], $height );
  
      $background_color = $this->RgbColor( $img, $this->options[ 'color_background' ] );
      $border_color     = $this->RgbColor( $img, $this->options[ 'color_border' ] );
      $font_color       = $this->RgbColor( $img, $this->options[ 'color_font' ] );
  
      switch($this->options['border_style']) {
        case 1:
          /*
           * fill image w/ background color
           */
          imagefill( $img, 0, 0, $background_color );
          /*
           * draw border
           * x, y, x, y
           */
          for($k=0; $k<$this->options['border_width']; $k++) {
            imagerectangle($img, $k, $k, $this->options[ 'width' ] - $k-1, $height - $k-1, $border_color);
          }
        break;
        case 2:
          // set transparent background color
    
          $transparent = imagecolorallocate( $img, 0xc0, 0xc0, 0xc0 );
    
          imagefilledrectangle( $img, 0, 0, $this->options[ 'width' ], $height, $transparent );
    
          imagecolortransparent( $img, $transparent );
          
          unset( $transparent ); 
    
          include_once( $this->path . '/lib/omnimage.lib.php' );
    
          imagefilledroundedrectangelborder( $img, 0, 0, $this->options[ 'width' ], $height, 15, $background_color, $border_color, $this->options[ 'border_width' ] );
        break;
        default:
          /*
           * fill image w/ background color
           */
          imagefill( $img, 0, 0, $background_color );
        break;
      }
  
      for($k=0; $k<count( $infos ); $k++) {
        imagestring( $img, 3, 10, $top + ($k*$line_padding), sprintf( '%s: %s', $this->Encode( $infos[ $k ][ 0 ] ), $infos[ $k ][ 1 ] ), $font_color );
      }
  
      $text =  'Statbadge v' . $this->version;
  
      imagestring( $img, 3, ($this->options[ 'width' ] / 2 ) - strlen( $text ) * 3.5, $height - 25, $text, $font_color );
      if(is_writeable($this->path. '/cache')) {
        imagegif($img, $this->cache_file);
      }
    }
    else {
      $img = imagecreatefromgif($this->cache_file);
    }
    
    header( 'Content-Type: image/gif' );
    // cache
    imagegif( $img );
  }
  
  function Encode( $s ) {
    if( function_exists( 'utf8_decode' ) ) {
      $s = utf8_decode( $s );
    }
    
    return $s;
  }
  
  function InitWidgets() {
    if(function_exists('register_sidebar_widget')) {
      register_sidebar_widget('Statbadge Widget', array(&$this, 'Widget'), null, 'widget_statbadge');
    }
  }
  
  function Widget($args) {
    extract($args);

    printf('%s%s%s%s%s%s', $before_widget, $before_title, $this->options['title'], $after_title, $this->GetBadgetTag(), $after_widget);
  }
}

function statbadge_display() {
  global $Statbadge;

  if(!isset($Statbadge)) {
    $Statbadge = new Statbadge();
  }

  if($Statbadge) {
    print($Statbadge->GetBadgetTag());
  }
}
endif;

if(@isset($_GET['statbadge'])) {
  include_once(dirname(__FILE__). '/../../../wp-config.php');

  if(!isset($Statbadge)) {
    $Statbadge = new Statbadge();
  }

  $Statbadge->Draw();
}
else {
  add_action('plugins_loaded', create_function('$Statbadge_sd2d22sa', 'global $Statbadge; $Statbadge = new Statbadge();')); 
}

?>
