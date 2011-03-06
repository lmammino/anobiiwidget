<?php

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

    public function __construct($id=null, $title=null, $subtitle=null, $format=null, $language=null, $cover=null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->format = $format;
        $this->language = $language;
        $this->cover = $cover;
    }

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

    public function the_id()
    {
        echo $this->id;
    }

    public function the_title()
    {
        echo $this->title;
    }

    public function the_subtitle()
    {
        echo $this->subtitle;
    }

    public function the_format()
    {
        echo $this->format;
    }

    public function the_language()
    {
        echo $this->language;
    }

    public function the_cover()
    {
        echo $this->cover;
    }

    public function the_progress()
    {
        echo $this->progress;
    }

    public function the_startDate($format = null)
    {
        if($format !== null)
            echo date($format, $this->startDate);
        else
            echo $this->startDate;
    }

    public function the_endDate($format = null)
    {
        if($format !== null)
            echo date($format, $this->endDate);
        else
            echo $this->endDate;
    }

    public function the_url()
    {
        echo $this->getUrl();
    }

    public static function slugify($text)
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