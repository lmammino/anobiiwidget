<?php
/**
 * @package anobiiwidget
 * @since 0.0.1
 */

/*
Plugin Name: AnobiiWidget
Plugin URI: http://oryzone.com/
Description: Allows you to show what you're reading on <a href="http://www.anobii.com">Anobii.com</a>
Version: 0.0.9
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


/** Loads the translations */
load_plugin_textdomain('anobiiwidget', false, dirname( plugin_basename(__FILE__) ) . '/languages');


/** Constants */
define('ANOBIIWIDGET_VERSION_KEY', 'anobiiwidget_version');
define('ANOBIIWIDGET_VERSION', '0.0.9');
define('ANOBIIWIDGET_APIKEY', '757b1f95970d049d12d8f96929de3439');
define('ANOBIIWIDGET_SIGNATURE', '4c150ed68347e023f1cce1295516ff28');


/** Checks if the plugin has been updated and calls the specific onUpdate handler */
if (get_option(ANOBIIWIDGET_VERSION_KEY) != ANOBIIWIDGET_VERSION) {
    AnobiiWidget::onUpdate();
}


/**
 * This file holds the AnobiiBook class, internally used to manage the book
 * objects
 */
require(dirname(__FILE__) . "/anobiiBook.php");


/**
 * Main widget class
 * @author Luciano Mammino <lmammino@oryzone.com>
 * @version 0.0.1
 */
class AnobiiWidget extends WP_Widget
{
    /* -- Request constants -- */
    /** base url for requesting the shelf */
    public static $SHELF_REQUEST = 'http://api.anobii.com/shelf/getSimpleShelf';
    /** base url for requesting infos about a book */
    public static $ELEMENT_REQUEST = 'http://api.anobii.com/item/getInfo';

    /* -- Options constants -- */
    /** number of elements available to show */
    public static $NUM_ITEMS_ARRAY = array(1,2,3,4,5);
    /** default number of elements to show */
    public static $NUM_ITEMS_DEFAULT = 5;
    /** reading progression options */
    public static $PROGRESS_ARRAY = array(
            /* populated at runtime */
    );
    /** default selected progression elements */
    public static $PROGRESS_DEFAULTS = array(1,3);
    /** default options for using javascript */
    public static $USE_JAVASCRIPT_DEFAULT = true;
    /** cache durations options */
    public static $CACHE_DURATIONS = array(
            /* populated at runtime */
    );
    /** default cache duration */
    public static $CACHE_DURATION_DEFAULT = "86400";
    /** show image options */
    public static $SHOW_IMAGES_OPTIONS = array(
        /* populated at runtime */
    );
    /** default show image option */
    public static $SHOW_IMAGES_DEFAULT = 1;
    /** default value for add profile link option */
    public static $ADD_PROFILE_LINK_DEFAULT = true;
    /** default value for the option to sponsorize the plugin */
    public static $SPONSORIZE_PLUGIN_DEFAULT = true;


    /**
     * Static function to register the widget
     */
    public static function register()
    {
        wp_enqueue_script( 'jquery' );
        register_widget("AnobiiWidget");
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        /* populates option arrays that need translations */
        self::$PROGRESS_ARRAY = array(
            "1" => __("Finished",'anobiiwidget'),
            "2" => __("Not Started",'anobiiwidget'),
            "3" => __("Reading",'anobiiwidget'),
            "4" => __("Unfinished",'anobiiwidget'),
            "5" => __("Reference",'anobiiwidget'),
            "6" => __("Abandoned",'anobiiwidget')
        );
        self::$CACHE_DURATIONS = array(
            "86400" => __("One day",'anobiiwidget'),
            "259200" => __("3 days",'anobiiwidget'),
            "604800" => __("A week",'anobiiwidget'),
            "2592000" => __("A month",'anobiiwidget')
        );
        self::$SHOW_IMAGES_OPTIONS = array(
            "0" => __("Always",'anobiiwidget'),
            "1" => __("Only on the first element",'anobiiwidget'),
            "2" => __("Never",'anobiiwidget')
        );

        parent::__construct('anobiiWidget', 'Anobii', array(
                'classname' => 'anobiiWidget',
                'description' => __("Shows your books on Anobii.com", 'anobiiwidget')));
    }


    /** @see WP_Widget::widget */
    function widget($args, $instance)
    {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        if(isset($instance['add_profile_link']) && $instance['add_profile_link'] == true)
        {
            $title = '<a href="http://www.anobii.com/'.$instance['user'].'/">'.$title.'</a>';
        }
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
        $instance['add_profile_link'] = (isset($new_instance['add_profile_link']))? (boolean)($new_instance['add_profile_link']) : false;
        $instance['num_items'] = in_array($new_instance['num_items'], self::$NUM_ITEMS_ARRAY) ?
                                 $new_instance['num_items'] : self::$NUM_ITEMS_DEFAULT;
        $instance['show_images'] = in_array($new_instance['show_images'], array_keys(self::$SHOW_IMAGES_OPTIONS)) ?
                                 $new_instance['show_images'] : self::$SHOW_IMAGES_DEFAULT;
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

        if (isset($new_instance['sponsorizePlugin']))
                $instance['sponsorizePlugin'] = (boolean)($new_instance['sponsorizePlugin']);
            else
                $instance['sponsorizePlugin'] = false;

        return $instance;
    }


    /** @see WP_Widget::form */
    function form($instance)
    {
        $title = esc_attr($instance['title']);
        $user = esc_attr($instance['user']);
        $num_items = esc_attr($instance['num_items']);
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','anobiiwidget'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('user'); ?>"><?php _e('Username:','anobiiwidget'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('user'); ?>" name="<?php echo $this->get_field_name('user'); ?>" type="text" value="<?php echo $user; ?>" />
        </p>
        <p>
          <input id="<?php echo $this->get_field_id('add_profile_link'); ?>"
                 name="<?php echo $this->get_field_name('add_profile_link'); ?>"
                 type="checkbox"
                 <?php if( (isset($instance['add_profile_link']) && $instance['add_profile_link'] == true)  ||
                             (!isset($instance['add_profile_link']) && self::$ADD_PROFILE_LINK_DEFAULT) ): ?>
                            checked="checked"
                 <?php endif; ?> />
          <label for="<?php echo $this->get_field_id('add_profile_link'); ?>"><?php _e('Add profile link','anobiiwidget'); ?></label>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('num_items'); ?>"><?php _e('Items:','anobiiwidget'); ?></label>
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
           <label for="<?php echo $this->get_field_id('show_images'); ?>"><?php _e('Show images:','anobiiwidget'); ?></label>
          <select class="widefat" id="<?php echo $this->get_field_id('show_images'); ?>" name="<?php echo $this->get_field_name('show_images'); ?>">
              <?php
              $current = isset($instance['show_images'])? $instance['show_images'] : self::$SHOW_IMAGES_DEFAULT;
              foreach(self::$SHOW_IMAGES_OPTIONS as $id => $name):
              ?>
              <option value="<?php echo $id ?>" <?php if($id==$current): ?>selected="selected"<?php endif; ?>>
                <?php echo $name ?>
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
            <label for="<?php echo $this->get_field_id('useJavascript'); ?>"><?php _e("Use Javascript (recommended)",'anobiiwidget') ?></label>
        </p>
            <label for="<?php echo $this->get_field_id('progressFieldSet'); ?>"><?php _e("Book types:",'anobiiwidget') ?></label>
            <fieldset class="widefat" id="<?php echo $this->get_field_id('progressFieldSet'); ?>">
            <?php foreach(self::$PROGRESS_ARRAY as $progressId => $progressName): ?>
                <input type="checkbox" id="<?php echo $this->get_field_id('progress-'.$progressId); ?>"
                       name="<?php echo $this->get_field_name('progress-'.$progressId); ?>"
                       <?php if( (isset($instance['progress-'.$progressId]) && $instance['progress-'.$progressId] == true)  ||
                             (!isset($instance['progress-'.$progressId]) && in_array($progressId, self::$PROGRESS_DEFAULTS)) ): ?>
                            checked="checked"
                       <?php endif; ?> />
                <label for="<?php echo $this->get_field_id('progress-'.$progressId); ?>"><?php echo $progressName ?></label><br/>
            <?php endforeach; ?>
            </fieldset>
        <p style="margin-top: 20px">
           <label for="<?php echo $this->get_field_id('cache_duration'); ?>"><?php _e('Cache duration:','anobiiwidget'); ?></label>
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
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id('sponsorizePlugin'); ?>"
                       name="<?php echo $this->get_field_name('sponsorizePlugin'); ?>"
                       <?php if( (isset($instance['sponsorizePlugin']) && $instance['sponsorizePlugin'] == true)  ||
                             (!isset($instance['sponsorizePlugin']) && self::$SPONSORIZE_PLUGIN_DEFAULT) ): ?>
                            checked="checked"
                       <?php endif; ?> />
            <label for="<?php echo $this->get_field_id('sponsorizePlugin'); ?>"><?php _e("Promote the plugin",'anobiiwidget') ?></label>
        </p>
        <?php
    }


    /**
     * Gets the content of a given widget using a given set of options.
     * It uses cache via transient to avoid sending and reprocessing the whole
     * anobii requests each time.
     * @param   int|String   $widgetNumber  the number of the widget
     * (you can use more anobiiwidget at the same time, every widget has it's
     * own number)
     * @param   Array    $options   an array of options (optional, it will load
     * the previously stored options for this widget if left blank)
     * @return  String              the html content of the widget
     */
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

        try
        {
            $books = self::requestShelf($options);

            $content = self::renderBooks($books, $options);

            set_transient($transientName, $content,
                    isset($options['cache_duration']) ? $options['cache_duration'] : self::$CACHE_DURATION_DEFAULT);
        }
        catch(Exception $e)
        {
            $content = __("aNobii servers are currently unavailable. Please try again later.");
        }

        return $content;
    }


    /**
     * Creates the html code for an array of <AnobiiBook> and a given set of options.
     * @param    Array  $books  an array of <AnobiiBooks>
     * @param    Array  $options    an array of options
     * @return   String             the html generated code
     */
    protected static function renderBooks($books, $options = array())
    {
        $html = '<!-- anobiiWidget v'.ANOBIIWIDGET_VERSION.' (http://wordpress.org/extend/plugins/anobiiwidget) developed by ORYZONE (http://oryzone.com)  -->';
        if(empty($books))
        {
            $html .= '<div class="anobiiwidget-nobooks">';
            $html .= __("There are no books to show", "anobiiwidget");
            $html .= '</div>';
            return $html;
        }
        
        $html .= '<ul class="anobiiwidget-list">';
        
        $first = true;
        
        foreach($books as $book)
        {
            $html .= '<li'. ( ($first === true)? ' class="first"' : '') . '><div class="anobiiwidget-book">';
            if( ($first && $options['show_images'] == 1) || $options['show_images'] == 0 )
            {
                $html .= '<div class="anobiiwidget-bookcover"><a title="'. $book->title .'" href="'. $book->url .'"><img src="'. $book->cover .'" alt="'. $book->title .'" /></a></div>';
                $first = false;
            }
            
            $html .= '<div class="anobiiwidget-title"><a title="'. $book->title .'" href="' . $book->url . '">'. $book->title .'</a></div>';
            $html .= '</div></li>';
        }

        $html .= '</ul>';

        if($options['sponsorizePlugin'])
            $html .= '<p>'. sprintf(__("%s developed by %s"),
                    "<a href=\"http://wordpress.org/extend/plugins/anobiiwidget\">aNobiiWidget</a>",
                    "<a href=\"http://oryzone.com\">ORYZONE</a>") . '</p>';

        return $html;
    }


    /**
     * Gets the name of the transient for a given widget (using the widget number).
     * Transient is used to cache the widget content.
     * @param   int|String  $number     the widget number
     * @return  String
     */
    protected static function getTransientName($number)
    {
        return 'anobiiwidget-' . $number. "-cached-html";
    }


    /**
     * Request the user shelf, processes the request and produce an array of
     * <AnobiiBook>.
     * @param   Array   $options    an array of options
     * @return  Array               an array of <AnobiiBook>
     */
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


        try 
        {
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
        }
        catch(Exception $e)
        {
            throw $e;
        }
        
        return $books;
    }


    /**
     * Make a request to retrieve the infos of a book
     * @param   String  $bookId     the id of the book
     * @return  AnobiiBook          an <AnobiiBook> object
     */
    protected static function requestBook($bookId)
    {
        $xml = null;
        try
        {
            $xml= self::doRequest(self::$ELEMENT_REQUEST, array( "item_id" => $bookId));
        }
        catch(Exception $e)
        {
            throw $e;
        }
        return AnobiiBook::fromXML($xml);
    }


    /**
     * Gets a part of the request query that provides the authorization parameters
     * @return  String
     */
    protected static function getApplicationAuthParams()
    {
        return 'api_key='.ANOBIIWIDGET_APIKEY.'&api_sig='.ANOBIIWIDGET_SIGNATURE;
    }


    /**
     * Function that executes a REST request and return the response from the server
     * @param   String  $url    the url
     * @param   Array   $params     an associative array of parameters to attach to the request
     * @return  String              the textual response of the server
     */
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

        if(!$data)
        {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }

        return $data;
    }

    
    /**
     * Function that handles plugin update business logic
     * @since 0.0.3
     */
    public static function onUpdate()
    {
        self::clearTransient();

        //stores the current version
        update_option(ANOBIIWIDGET_VERSION_KEY, ANOBIIWIDGET_VERSION);
    }


    /**
     * Function that removes all stored preferences and cache when uninstalling
     * the plugin
     * @since 0.0.5
     */
    public static function onUninstall()
    {
        delete_option(ANOBIIWIDGET_VERSION_KEY);
        delete_option('widget_anobiiwidget');
        self::clearTransient();
    }


    /**
     * Function that removes all the transient data created by the plugin
     * @since 0.0.5
     */
    protected static function clearTransient()
    {
        //quick and dirty way to force the cache clearing
        //please suggest any better solution if you know
        for($i=1; $i < 11; $i++)
            delete_transient (self::getTransientName ($i));
    }
}


/**
 * Ajax handling function to retrieve the widget content
 */
function anobiiwidget_ajax_get_content()
{
    if(isset($_POST['widget_number']))
    {
        echo AnobiiWidget::getContent($_POST['widget_number']);
        exit();
    }
}


/* attach the ajax handler function */
add_action("wp_ajax_anobiiwidget_get_content", "anobiiwidget_ajax_get_content");
add_action("wp_ajax_nopriv_anobiiwidget_get_content", "anobiiwidget_ajax_get_content");

/* attach the function that registers the widget */
add_action('widgets_init', "AnobiiWidget::register");

/* attach the uninstal hook */
register_uninstall_hook(__FILE__, "AnobiiWidget::onUninstall");

?>