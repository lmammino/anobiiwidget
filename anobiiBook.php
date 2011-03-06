<?php

/**
 * Class used to structure the information related to a book.
 * Part of the anobiWidget wordpress plugin.
 * @author Luciano Mammino <lmammino@oryzone.com>
 */
class AnobiiBook
{
    public $id;
    public $title;
    public $subtitle;
    public $format;
    public $language;
    public $cover;
    public $progress;
    public $startDate;
    public $endDate;


    /**
     * Constructor
     * @param String $id the book id
     * @param String $title the book title
     * @param String $subtitle the book subtitle
     * @param String $format the book format
     * @param String $language the book language
     * @param String $cover the url of the book cover
     */
    public function __construct($id=null,
                                $title=null,
                                $subtitle=null,
                                $format=null,
                                $language=null,
                                $cover=null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->format = $format;
        $this->language = $language;
        $this->cover = $cover;
    }


    /**
     * Gets the url of the book by processing the book cover (couse anobii API
     * actually does not offer a better method!)
     * @return String
     */
    public function getUrl()
    {
          $var  = parse_url($this->cover, PHP_URL_QUERY);
          $var  = html_entity_decode($var);
          $var  = explode('&', $var);
          $arr  = array();
          foreach($var as $val)
           {
            $x = explode('=', $val);
            $arr[$x[0]] = $x[1];
           }
          unset($val, $x, $var);
        
        return "http://www.anobii.com/books/". self::slugify($this->title) . "/" . $arr['item_id'] . "/";
    }


    /**
     * Function used to generate an url friendly slug for the book title.
     * Taken from http://www.symfony-project.org/jobeet/1_2/Propel/en/08
     * @param String $text the string to slugify
     * @return String a slugified string
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
     * @param String $xml
     * @return AnobiiBook a book filled with the information retrieved from the
     * given XML
     */
    public static function fromXML($xml)
    {
        $book = new AnobiiBook();

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $node = $dom->getElementsByTagName("item")->item(0);
        
        $book->id = $node->getAttribute("id");
        $book->title = $node->getAttribute("title");
        $book->subtitle = $node->getAttribute("subtitle");
        $book->format = $node->getAttribute("format");
        $book->language = $node->getAttribute("language");
        $book->cover = urldecode($node->getAttribute("cover"));

        return $book;
    }

}
?>