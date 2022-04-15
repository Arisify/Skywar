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

namespace arie\skywar\utils;

class Money{
	public static function serialize(int|float $balance, int $decimals = 0, string $decimal_separator = ".", string $thousands_separator = ",") : string{
		return number_format($balance, $decimals, $decimal_separator, $thousands_separator);
	}
}