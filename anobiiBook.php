<?php
/**
 * @package anobiiwidget
 * @since 0.0.1
 */

/**
 * Class used to structure the information related to a book.
 * Part of the anobiWidget wordpress plugin.
 * @author Luciano Mammino <lmammino@oryzone.com>
 */
class AnobiiBook
{
    public $id;
    public $title;
    public $url;
    public $subtitle;
    public $format;
    public $language;
    public $cover;
    public $progress;
    public $startDate;
    public $endDate;
    public static $DEFAULT_COVER = "http://static.anobii.com/anobii/static/image/no_image_large_text.gif";


    /**
     * Constructor
     * @param   String  $id     the book id
     * @param   String  $title  the book title
     * @param   String  $url    the book url on anobii.com
     * @param   String  $subtitle   the book subtitle
     * @param   String  $format     the book format
     * @param   String  $language   the book language
     * @param   String  $cover  the url of the book cover
     */
    public function __construct($id=null,
                                $title=null,
                                $url=null,
                                $subtitle=null,
                                $format=null,
                                $language=null,
                                $cover=null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->url = $url;
        $this->subtitle = $subtitle;
        $this->format = $format;
        $this->language = $language;
        $this->cover = $cover;
    }


    /**
     * Gets the url of the book by processing the book cover (couse anobii API
     * actually does not actually offer a better method!)
     * @return  String|boolean      the url of the book or the boolean false
     * in case of error
     * @since 0.0.2
     */
    protected function extractUrl()
    {
            $var  = parse_url($this->cover, PHP_URL_QUERY);
          $var  = html_entity_decode($var);
          $var  = explode('&', $var);
          $arr  = array();
          foreach($var as $val)
          {
            $x = explode('=', $val);
            if(isset($x[1]))
                $arr[$x[0]] = $x[1];
            else
                $arr[$x[0]] = null;
          }
          unset($val, $x, $var);

          if(!isset($arr['item_id']))
              return false;

        return "http://www.anobii.com/books/". self::slugify($this->title) . "/" . $arr['item_id'] . "/";
    }


    /**
     * Function used to generate an url friendly slug for the book title.
     * Taken from http://www.symfony-project.org/jobeet/1_2/Propel/en/08
     * @param   String  $text   the string to slugify
     * @return  String          a slugified string
     */
    protected static function slugify($text)
    {
      // replace non letter or digits by -
      $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

      // trim
      $text = trim($text, '-');

      // transliterate
      if (function_exists('iconv'))
      {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
      }

      // lowercase
      $text = strtolower($text);

      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);

      if (empty($text))
      {
        return 'n-a';
      }

      return $text;
    }


    /**
     * Processes a piece of XML and extract books informations from it
     * @param   String  $xml The piece of XML from wich to create the book instance
     * @return  AnobiiBook      a book filled with the information retrieved from the
     * given XML
     */
    public static function fromXML($xml)
    {
        $book = new AnobiiBook();
        
        $xml = str_replace("& ", "&amp; ", $xml);
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $node = $dom->getElementsByTagName("item")->item(0);
        
        $book->id = $node->getAttribute("id");
        $book->title = $node->getAttribute("title");
        $book->subtitle = $node->getAttribute("subtitle");
        $book->format = $node->getAttribute("format");
        $book->language = $node->getAttribute("language");
        $book->cover = urldecode($node->getAttribute("cover"));
        $book->url = $book->extractUrl($book->url);

        if (!$book->hasRealCover())
            $book->cover = self::$DEFAULT_COVER;

        return $book;
    }


    /**
     * Checks if the book has really a cover using a curl request and by checking
     * the response code.
     * Anoobii api don't actually seem to provide a better method to determinate
     * wheter the book has a cover or not.
     * @return   boolean     true if the book has a real cover, false otherwise
     * @since 0.0.2
     */
    public function hasRealCover()
    {
        if(empty($this->cover))
            return false;

        $curl = curl_init($this->cover);

        //don't fetch the actual page, you only want to check the connection is ok
        curl_setopt($curl, CURLOPT_NOBODY, true);

        //do request
        $result = curl_exec($curl);

        $ret = false;

        //if request did not fail
        if ($result !== false) {
            //if request was ok, check response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($statusCode == 200) {
                $ret = true;
            }
        }

        curl_close($curl);

        return $ret;
    }

}
?>