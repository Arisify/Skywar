<?php
declare(strict_types=1);

namespace arie\skywar\utils;

class Time{
	public static function serialize(int $seconds) : string{
		return gmdate('H:i:s', $seconds);
	}
}
