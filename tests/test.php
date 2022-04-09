<?php
$loops = 50000000;
foreach ($funcs as $func) {
	for ($i = 0; $i < $loops; $i++) {
		$func["f"]();
	}

	echo sprintf("%.2f s <- %s%s", microtime(true) - $time, $func["n"], PHP_EOL);
	$time = microtime(true);
}