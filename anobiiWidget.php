<?php
/**
 * @package Akismet
 */
/*
Plugin Name: AnobiiWidget
Plugin URI: http://oryzone.com/
Description: Allows you to show what you're reading on <a href="http://www.anobii.com">Anobii.com</a>
Version: 0.0.1
Author: Luciano Mammino
Author URI: http://oryzone.com
License: GPLv2
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('ANOBIIWIDGET_VERSION', '0.0.1');
define('ANOBIIWIDGET_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('ANOBIIWIDGET_APIKEY', '757b1f95970d049d12d8f96929de3439');
define('ANOBIIWIDGET_SIGNATURE', '4c150ed68347e023f1cce1295516ff28');


require(dirname(__FILE__) . "/anobiiBook.php");

class AnobiiWidget extends WP_Widget
{

    public static $SHELF_REQUEST = 'http://api.anobii.com/shelf/getSimpleShelf';
    public static $ELEMENT_REQUEST = 'http://api.anobii.com/item/getInfo';
    public static $NUM_ITEMS_ARRAY = array(1,2,3,4,5);
    public static $NUM_ITEMS_DEFAULT = 5;
    public static $PROGRESS_ARRAY = array(
            "1" => "Finished",
            "2" => "Not Started",
            "3" => "Reading",
            "4" => "Unfinished",
            "5" => "Reference",
            "6" => "Abandoned"
    );
    public static $PROGRESS_DEFAULTS = array(1,3);
    public static $USE_JAVASCRIPT_DEFAULT = true;
    public static $CACHE_DURATIONS = array(
            "86400" => "One day",
            "259200" => "3 days",
            "604800" => "A week",
            "2592000" => "A month"
    );
    public static $CACHE_DURATION_DEFAULT = "86400";

    
    public static function register()
    {
        register_widget("AnobiiWidget");
    }


    public static function getApplicationAuthParams()
    {
        return 'api_key='.ANOBIIWIDGET_APIKEY.'&api_sig='.ANOBIIWIDGET_SIGNATURE;
    }

    
    public function __construct()
    {
        parent::__construct('anobiiWidget', 'Anobii', array(
                'classname' => 'anobiiWidget',
                'description' => 'Shows what you\'re reading on Anobii.com' ));
    }


    /** @see WP_Widget::widget */
    function widget($args, $instance)
    {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; ?>
                        <div id="anobiiwidget-content-<?php echo $this->number ?>">
                            <?php if($instance['useJavascript']): ?>
                                <span class="anobiiwidget-loading">Loading...</span>
                            <?php else: ?>
                                <?php echo self::getContent($this->number, $instance) ?>
                            <?php endif; ?>
                        </div>
                        <?php if($instance['useJavascript']): ?>
                            <script type="text/javascript">
                                jQuery(function($){
                                    $.post( "<?php echo get_bloginfo('wpurl') ?>/wp-admin/admin-ajax.php" ,
                                            { action:"anobiiwidget_get_content","widget_number": <?php echo $this->number ?> } ,
                                            function(data){
                                                $('#anobiiwidget-content-<?php echo $this->number ?>').html(data);
                                            }
                                    );
                                });
                            </script>
                        <?php endif; ?>
              <?php echo $after_widget; ?>
        <?php
    }


    /** @see WP_Widget::update */
    function update($new_instance, $old_instance)
    {
        delete_transient(self::getTransientName($_POST['widget_number']));
        $instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
        $instance['user'] = strip_tags($new_instance['user']);
        $instance['num_items'] = in_array($new_instance['num_items'], self::$NUM_ITEMS_ARRAY) ?
                                 $new_instance['num_items'] : self::$NUM_ITEMS_DEFAULT;
        foreach(self::$PROGRESS_ARRAY as $progressId => $progressName)
        {
            if (isset($new_instance['progress-'.$progressId]))
                $instance['progress-'.$progressId] = (boolean)($new_instance['progress-'.$progressId]);
            else
                $instance['progress-'.$progressId] = false;
        }

        if (isset($new_instance['useJavascript']))
                $instance['useJavascript'] = (boolean)($new_instance['useJavascript']);
            else
                $instance['useJavascript'] = false;

        if(isset($new_instance['cache_duration']) && in_array($new_instance['cache_duration'], array_keys(self::$CACHE_DURATIONS)))
                $instance['cache_duration'] = $new_instance['cache_duration'];

        return $instance;
    }


    /** @see WP_Widget::form */
    function form($instance)
    {
        $title = esc_attr($instance['title']);
        $user = esc_attr($instance['user']);
        $num_items = esc_attr($instance['num_items']);
        echo self::getTransientName($this->number);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('user'); ?>"><?php _e('Username:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('user'); ?>" name="<?php echo $this->get_field_name('user'); ?>" type="text" value="<?php echo $user; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('num_items'); ?>"><?php _e('Items:'); ?></label>
          <select class="widefat" id="<?php echo $this->get_field_id('num_items'); ?>" name="<?php echo $this->get_field_name('num_items'); ?>">
              <?php
              $current = isset($instance['num_items'])? $instance['num_items'] : self::$NUM_ITEMS_DEFAULT;
              foreach(self::$NUM_ITEMS_ARRAY as $num):
              ?>
              <option value="<?php echo $num ?>" <?php if($num==$current): ?>selected="selected"<?php endif; ?>>
                <?php echo $num ?>
              </option>
              <?php endforeach; ?>
          </select>
        </p>
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id('useJavascript'); ?>"
                       name="<?php echo $this->get_field_name('useJavascript'); ?>"
                       <?php if( (isset($instance['useJavascript']) && $instance['useJavascript'] == true)  ||
                             (!isset($instance['useJavascript']) && self::$USE_JAVASCRIPT_DEFAULT) ): ?>
                            checked="checked"
                       <?php endif; ?> />
            <label for="<?php echo $this->get_field_id('useJavascript'); ?>"><?php _e("Use Javascript") ?></label>
        </p>
            <label for="<?php echo $this->get_field_id('progressFieldSet'); ?>"><?php _e("Book types:") ?></label>
            <fieldset class="widefat" id="<?php echo $this->get_field_id('progressFieldSet'); ?>">
            <?php foreach(self::$PROGRESS_ARRAY as $progressId => $progressName): ?>
                <input type="checkbox" id="<?php echo $this->get_field_id('progress-'.$progressId); ?>"
                       name="<?php echo $this->get_field_name('progress-'.$progressId); ?>"
                       <?php if( (isset($instance['progress-'.$progressId]) && $instance['progress-'.$progressId] == true)  ||
                             (!isset($instance['progress-'.$progressId]) && in_array($progressId, self::$PROGRESS_DEFAULTS)) ): ?>
                            checked="checked"
                       <?php endif; ?> />
                <label for="<?php echo $this->get_field_id('progress-'.$progressId); ?>"><?php _e($progressName) ?></label><br/>
            <?php endforeach; ?>
            </fieldset>
        <p>
           <label for="<?php echo $this->get_field_id('cache_duration'); ?>"><?php _e('Cache duration:'); ?></label>
          <select class="widefat" id="<?php echo $this->get_field_id('cache_duration'); ?>" name="<?php echo $this->get_field_name('cache_duration'); ?>">
              <?php
              $current = isset($instance['cache_duration'])? $instance['cache_duration'] : self::$CACHE_DURATION_DEFAULT;
              foreach(self::$CACHE_DURATIONS as $duration => $name):
              ?>
              <option value="<?php echo $duration ?>" <?php if($duration==$current): ?>selected="selected"<?php endif; ?>>
                <?php echo $name ?>
              </option>
              <?php endforeach; ?>
          </select>
        </p>
        <?php
    }


    public static function getContent($widgetNumber, $options = null)
    {
        if($options === null)
        {
            $options = get_option('widget_anobiiwidget');
            $options = $options[$widgetNumber];
        }

        $transientName = self::getTransientName($widgetNumber);

        $data = get_transient( $transientName );
        if($data !== false)
                return $data;

        $books = self::requestShelf($options);

        $content = self::renderBooks($books, $options);

        set_transient($transientName, $content,
                isset($options['cache_duration']) ? $options['cache_duration'] : self::$CACHE_DURATION_DEFAULT);

        return $content;
    }

    protected static function renderBooks($books, $options = array())
    {
        if(empty($books))
        {
            $html = '<div class="anobiiwidget-nobooks">';
            $html .= __("There are no books to show");
            $html .= '</div>';
            return $html;
        }
        
        $html = '<ul class="anobiiwidget-list">';
        
        $first = true;
        
        foreach($books as $book)
        {
            $html .= '<li><div class="anobiiwidget-book">';
            if($first)
            {
                $html .= '<div class="anobiiwidget-bookcover"><a  href="'. $book->getUrl() .'"><img src="'. $book->cover .'" alt="'. $book->title .'" /></a></div>';
                $first = false;
            }
            
            $html .= '<div class="anobiiwidget-title"><a href="' . $book->getUrl() . '">'. $book->title .'</a></div>';
            $html .= '</div></li>';
        }

        $html .= '</ul>';

        return $html;
    }




    protected static function getTransientName($number)
    {
        return 'anobiiwidget-' . $number. "-cached-html";
    }


    protected static function requestShelf($options)
    {
        $books = array();

        $progress = array();
        foreach(self::$PROGRESS_ARRAY as $progressId => $progressname)
        {
            if(isset($options['progress-'.$progressId]) && $options['progress-'.$progressId] == true)
                $progress[] = $progressId;
        }

        if(!empty($progress))
            $progress = implode(",", $progress);



        $xml = self::doRequest(self::$SHELF_REQUEST, array(
                      "user_id" => $options['user'],
                      "limit" => $options['num_items'],
                      "progress" => $progress
                  ));

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        foreach($dom->getElementsByTagName("item") as $item)
        {
            $book = self::requestBook($item->getAttribute("id"));
            $book->progress = $item->getAttribute("progress");
            $book->startDate = $item->getAttribute("start_date");
            $book->endDate = $item->getAttribute("end_date");

            $books[] = $book;
        }
        
        return $books;
    }


    protected static function requestBook($bookId)
    {
        $xml= self::doRequest(self::$ELEMENT_REQUEST, array( "item_id" => $bookId));
        return AnobiiBook::fromXML($xml);
    }
    

    protected static function doRequest($url, $params = array())
    {
        $fullUrl = $url . "?" . self::getApplicationAuthParams();
        foreach($params as $paramName => $paramValue)
            if(!empty($paramValue))
                $fullUrl .= "&" . $paramName . '=' . $paramValue;

        $ch = curl_init($fullUrl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

}

function anobiiwidget_ajax_get_content()
{
    if(isset($_POST['widget_number']))
    {
        echo AnobiiWidget::getContent($_POST['widget_number']);
        exit();
    }
}

add_action("wp_ajax_anobiiwidget_get_content", "anobiiwidget_ajax_get_content");
add_action("wp_ajax_no_priv_anobiiwidget_get_content", "anobiiwidget_ajax_get_content");



add_action('widgets_init', "AnobiiWidget::register");

?>