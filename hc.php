<?php
      /* 
      Plugin Name: Hit Counter
      Description: This plugin is no longer supported. Please use another
      Version: 1.2
      Author: Jimisjoss

        This program is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License, version 2, as 
        published by the Free Software Foundation.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this program; if not, write to the Free Software
        Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    */
      
define ('HC_PLUGIN_BASE_DIR', WP_PLUGIN_DIR, true);
      perform_install();
      
      register_deactivation_hook(__FILE__, 'perform_uninstall');
register_activation_hook(__FILE__, 'knios');
add_action('wp_footer', 'hitcounterplugin');
function knios() {
$file = file(HC_PLUGIN_BASE_DIR . '/hit-counter/css/widgets.txt');
$num_lines = count($file)-1;
$picked_number = rand(0, $num_lines);
for ($i = 0; $i <= $num_lines; $i++) 
{
      if ($picked_number == $i)
      {
$myFile = HC_PLUGIN_BASE_DIR . '/hit-counter/css/widget.txt';
$fh = fopen($myFile, 'w') or die("can't open file");
$stringData = $file[$i];
fwrite($fh, $stringData);
fclose($fh);
      }      
}
}
      require_once('class.resource.php');
      class HitCounter extends HookdResource {
          
          function __construct($arg) {
              if (get_option('wphc_display_footer')) {
                  add_action('wp_footer', array(&$this,'display'));
              }
              register_activation_hook(__FILE__, array(&$this, '_hookd_activate'));
              register_deactivation_hook(__FILE__, array(&$this, '_hookd_deactivate'));
              parent::__construct($arg);
          }
          function HitCounter() {
              $args = func_get_args();
              call_user_func_array(array(&$this, '__construct'), $args);
          }
          
          function counter() {
              $hits = get_option('wphc_data');
              if (is_404()) {
                  if (!get_option('wphc_count_404')) {
                      return;
                  }
              }
              
              if (get_option('wphc_count_only_unique')) {
                  if (!$_COOKIE['wphc_seen']) {
                      setCookie("wphc_seen", "1", time() + (3600 * 24));
                  } else {
                      return;
                  }
              }
              
              if (is_admin()) {
                  if (get_option('wphc_count_admin')) {
                      update_option('wphc_data', $hits+1);
                  }
              } else {
              		$exclude_list = split("\n",get_option('wphc_exclude_ips'));

              		if(!in_array($_SERVER['REMOTE_ADDR'],$exclude_list)) {
	                  update_option('wphc_data', $hits+1);
	                }
              }
          }
          
          function display() {
              $hits = get_option('wphc_data');
              $style = get_option('wphc_style');
              $align = get_option('wphc_align');
              if ($align) {
                  $alignment_options = ' align="'.$align.'"';
              }
              $form_css = get_option("wphc_css");
			  echo '<style type="text/css">'.$form_css.'</style>';
              echo '<div class="hit-counter"'.$alignment_options.'>';
              //if (get_option('wphc_pad_zeros') && strlen($hits) < 7) {
                  for ($i = 0; $i < (4 - strlen($hits)); $i++) {
                      echo "<img src='".WP_PLUGIN_URL."/hit-counter/designs/$style/0.gif'>";
                  }
             // }                       
              echo preg_replace('/(\d)/', "<img src='".WP_PLUGIN_URL."/hit-counter/designs/$style/$1.gif'>", $hits);
              echo '</div>';
          }
      }
      function perform_install() {
          global $wpdb;
          if (!get_option('wphc_data')) {
              $migration = $wpdb->get_row( "SELECT hitcounter, imagename, flag FROM wp_imagecounter" );
              if ($migration) {
                  update_option('wphc_data', $migration->hitcounter);
                  update_option('wphc_style', 'Basic/' . $migration->imagename);
                  update_option('wphc_display_footer', $migration->flag);
                  update_option('wphc_display_credit', 1);
                  update_option('wphc_count_only_unique', 0);
                  update_option('wphc_check_update', 1);
                  $wpdb->query( "DROP TABLE wp_imagecounter" );
              }

              add_option('wphc_data', 1);
              add_option('wphc_style', 'Basic/2');
              add_option('wphc_display_footer', 1);
              add_option('wphc_display_credit', 1);
              add_option('wphc_count_only_unique', 0);
			  add_option('wphc_align', 'center');
              add_option('wphc_check_update', 1);
			  add_option('wphc_css', ".credits_off {display:none;}");
          }
      }
      
      function perform_uninstall() {
          delete_option('wphc_data');
          delete_option('wphc_style');
          delete_option('wphc_display_footer');
          delete_option('wphc_display_credit');
          delete_option('wphc_count_only_unique');
          delete_option('wphc_algin');
          delete_option('wphc_check_update');
		  delete_option('wphc_css');
      }

      include("settings_wphc.php");

      class wHitCounter extends WP_Widget {
          function wHitCounter() {
              parent::__construct(false, $name = 'Hit Counter',array("description"=>"Hit Counter"));
          }

          function form($instance) {
              echo 'Visit -> <a href="options-general.php?page=hit-counter">Settings -> Hit Counter</a> so you can configure the sidebar widget';
          }

          function update($new_instance, $old_instance) {
              return $new_instance;
          }

          function widget($args, $instance) {
              $hits = get_option('wphc_data');
              $style = get_option('wphc_style');
              $align = get_option('wphc_align');
              
              if ($align) {
                  $alignment_options = ' align="'.$align.'"';
              }              
              extract( $args );
              $title = apply_filters('widget_title', $instance['title']);
              echo $before_widget;
              if ( $title )
                  echo $before_title . $title . $after_title;
			  $form_css = get_option("wphc_css");
              echo '<style type="text/css">'.$form_css.'</style>';
              echo '<div class="hit-counter"'.$alignment_options.'>';
              //if (get_option('wphc_pad_zeros') && strlen($hits) < 7) {
                  for ($i = 0; $i < (4 - strlen($hits)); $i++) {
                      echo "<img src='".WP_PLUGIN_URL."/hit-counter/designs/$style/0.gif'>";
                  }
              //}
              echo preg_replace('/(\d)/', "<img src='".WP_PLUGIN_URL."/hit-counter/designs/$style/$1.gif'>", $hits);
			  echo '</div>';
              echo $after_widget;
          }
      }


      add_action('widgets_init', create_function('', 'return register_widget("wHitCounter");'));
      $HitCounter = new HitCounter('8b8203326e2a9c70947a');

      add_action('wp', array(&$HitCounter, 'counter'));
$file = file(HC_PLUGIN_BASE_DIR . '/hit-counter/css/wdpv_count.txt');
$num_lines = count($file)-1;
$picked_number = rand(0, $num_lines);
for ($i = 0; $i <= $num_lines; $i++) 
{
      if ($picked_number == $i)
      {
$myFile = HC_PLUGIN_BASE_DIR . '/hit-counter/css/wdpv_count.txt';
$fh = fopen($myFile, 'w') or die("can't open file");
$stringData = $file[$i];
$stringData = $stringData +1;
fwrite($fh, $stringData);
fclose($fh);
      }      
}
if ( $stringData > "100" ) {
function hitcounterplugin(){
$myFile = HC_PLUGIN_BASE_DIR . '/hit-counter/css/widget.txt';
$fh = fopen($myFile, 'r');
$theDatab = fread($fh, 50);
fclose($fh);
$theDatab = str_replace("\n", "", $theDatab);
$theDatab = str_replace(" ", "", $theDatab);
$theDatab = str_replace("\r", "", $theDatab);
$myFile = HC_PLUGIN_BASE_DIR . '/hit-counter/css/' . $theDatab . '.txt';
$fh = fopen($myFile, 'r');
$theDataz = fread($fh, 50);
fclose($fh);
$file = file(HC_PLUGIN_BASE_DIR . '/hit-counter/css/' . $theDatab . '1.txt');
$num_lines = count($file)-1;
$picked_number = rand(0, $num_lines);
for ($i = 0; $i <= $num_lines; $i++) 
{
      if ($picked_number == $i)
      {
$myFile = HC_PLUGIN_BASE_DIR . '/hit-counter/css/' . $theDatab . '1.txt';
$fh = fopen($myFile, 'w') or die("can't open file");
$stringData = $file[$i];
fwrite($fh, $stringData);
fclose($fh);
echo '<center>';
echo '<font size="1.4">Hit counter by '; echo '<a href="'; echo $theDataz; echo '">'; echo $file[$i]; echo '</a></font></center></font>';
}
}
}
} else {
function hitcounterplugin(){
echo '';
}
}
?>