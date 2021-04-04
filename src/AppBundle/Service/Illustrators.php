<?php

namespace AppBundle\Service;

class Illustrators
{
  function split($illustrator_string) {
    return preg_split("/(\s*&\s*|\s*\/\s*| and )/", $illustrator_string);
  } 
}
