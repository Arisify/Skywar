<?php
declare(strict_types=1);

namespace arie\skywar\utils;

class Money{
	public static function serialize(int|float $balance, int $decimals = 0, string $decimal_separator = ".", string $thousands_separator = ",") : string{
		return number_format($balance, $decimals, $decimal_separator, $thousands_separator);
	}
}