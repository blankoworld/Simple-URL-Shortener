<?php

/** Base10Convert
  * Copyright (C) 2010 Hyacinthe Cartiaux <hyacinthe.cartiaux@free.fr>
  *
  * This program is free software: you can redistribute it and/or modify
  * it under the terms of the GNU Affero General Public License as
  * published by the Free Software Foundation, either version 3 of the
  * License, or (at your option) any later version.
  *
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU Affero General Public License for more details.

  * You should have received a copy of the GNU Affero General Public License
  * along with this program.  If not, see <http://www.gnu.org/licenses/>.
  */

error_reporting(E_ALL);

/**
 * @brief This class converts a number in base 10 to base n.
 **/
class Base10Convert
{

  private $alphabet;
  private $reversed_alphabet;
  private $base;

  /**
   * @brief Defines base and alphabet used to encode numbers
   *
   * @param base Integer, base Defaults to 62.
   * @param alphabet Array containing characters of the alphabet Defaults to URI unreserved characters (except -~._) in RFC 3985, section 2.3.
   **/
  public function __construct($base = "", $alphabet = array())
  {

    if ($base == "" && count($alphabet) == 0)
    {
      //RFC 3986 section 2.3 Unreserved Characters (January 2005)
      $this->alphabet = array('0','1','2','3','4','5','6','7','8','9',
			      'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
			      'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'
			      /* , '-','_','.','~' */
			    );

      $this->base = 62;
    }
    else if ($base == count($alphabet) && $base >= 2)
    {
      $this->alphabet = $alphabet;
      $this->base     = $base;
    }
    else
    {
      throw new Exception("Bad parameters");
    }

    $this->reversed_alphabet = array_flip($this->alphabet);

  }

  /**
   * @brief Encodes a number from base 10 to the given base
   *
   * @param integer Number to encode
   * @return String containing the encoded number
   **/
  public function encode($integer)
  {
    if (!is_int($integer))
      throw new Exception("Paremeter is not an integer");
    if ($integer == 0)
      return $this->alphabet[0];

    $encoded_nbr = "";

    while ($integer > 0)
    {
      $current_nbr = $integer % $this->base;
      $integer = (int) ($integer / $this->base);
      $encoded_nbr = $this->alphabet[$current_nbr] . $encoded_nbr;
    }

    return $encoded_nbr;
  }

  /**
   * @brief Decodes a number from the given base the base 10
   *
   * @param encoded_nbr Encoded number 
   * @return int
   **/
  public function decode($encoded_nbr)
  {
    $len = strlen($encoded_nbr);

    $decoded_number = 0;

    for ($i = 0 ; $i < $len ; $i++)
    {
      $char = substr($encoded_nbr, $i, 1);

      $power = $len - ($i + 1);
      if (isset($this->reversed_alphabet[$char]))
      {
        $decoded_number += $this->reversed_alphabet[$char] * pow($this->base, $power);
      }
      else
      {
        throw new Exception("Parameter contains a character not in the alphabet");
      }
    }

    return $decoded_number;
  }

}

/* Testing

// $obj = new Base10Convert("2", array('0', '1'));

$obj = new Base10Convert();

$str = $obj->encode(16165);
echo $str."\n";
$int = $obj->decode($str);
echo $int;

*/

?>