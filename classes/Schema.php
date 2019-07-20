<?php namespace Arcane\Seo\Classes;

class Schema {

  function __construct(string $type, array $properties)
  {
    $this->obj = [
      "@context" =>  "http://schema.org",
      "@type" => $type
    ];

    $this->obj = array_merge($this->obj, $this->getProperties($properties));
  }

  function getProperties($props) {
    $result = [];

    foreach ($props as $key => $prop ) {
      // if it encounters a @ in the key eg: author@Person
      if(preg_match('/(\w+)@(\w+)(\[\])*/', $key, $output)) {
        // add @type to the obj.
        if(!isset($output[3])) // if [] is not present
        {
          $prop = array_merge([
            "@type"=> $output[2] 
            // use recursion
          ], $this->getProperties($prop));

        } else {

          $prop = array_map(function($prop) use ($output) {
            return array_merge([
              "@type" => $output[2]
            ], $this->getProperties($prop));
          }, $prop);
        }

        $key= $output[1];
      }
      $result[$key] = $prop;
    }

    return $result;
  }

  function __toString() {
    return json_encode($this->obj) ;
  }

  static function toScript($yaml = "") {
    if (!$yaml) return;
    $array = \Yaml::parse($yaml);
    $str = "";

    foreach($array as $key => $properties) {
      $str .= 
        "<script type=\"application/ld+json\">" 
        . new self($key, $properties) 
        . "</script>"
        . "\r\r";
    }

    return $str;
  }
}
