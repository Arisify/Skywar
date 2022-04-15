<?php
/*
 * Copyright (c) 2022 Arisify
 *
 * This program is freeware, so you are free to redistribute and/or modify
 * it under the conditions of the MIT License.
 *
 * @author Arisify
 * @link   https://github.com/Arisify
 * @license https://opensource.org/licenses/MIT MIT License
 *
 * \    /\
 *  )  ( ') ᵐᵉᵒʷˢ
 * (  /  )
 *  \(__)|
 *
*/
declare(strict_types=1);

namespace arie\skywar\math;

class Vector3 extends \pocketmine\math\Vector3{
	public function __toString(){
		return "$this->x,$this->y,$this->z";
	}

	public static function fromString(string $string) : Vector3{
		$ob = array_map(static fn(string $string) : int => (int) $string, explode($string, ' '));
		return new Vector3($ob[0], $ob[1], $ob[2]);
	}
}
