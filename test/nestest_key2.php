<?php

$docs = [
	"# This is a doc",
	"# This is too",
	"haha: text",
	"  test:",
	"    Val: r",
	"    Vol:",
	"      New: r",
	"  new:",
	"    go: r",
	"u: text #Lmao ##ROFT"
];
$space = 0;
$key = " ";
$spaces = [];
foreach ($docs as $line) {
	$l = ltrim($line);
	$colon_pos = strpos($l, ':');
	if ($colon_pos === false) continue;
	$spaces[$key] = $space;
	$space = strlen($line) - strlen($l);
	if ($space === 0) {
		print("1" . PHP_EOL);
		$key = mb_substr($l, 0, $colon_pos);
	} else {
		if ($space > $spaces[$key]) {
			print("2-1" . PHP_EOL);
			$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
		} else {
			print("2-2" . PHP_EOL);
			if ($spaces[$key] > $space) {
				print("2-1-1" . PHP_EOL);
				while ($spaces[$key] >= $space) {
					$last_dotpos = strrpos($key, '.');
					if ($spaces[$key] === $space) {
						print("2-1-1-1" . PHP_EOL);
						$key = mb_substr($key, 0, $last_dotpos);
						$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
						break;
					}
					$key = mb_substr($key, 0, $last_dotpos);
				}
			} else {
				print("2-1-2" . PHP_EOL);
				$last_dotpos = strrpos($key, '.');
				$key = mb_substr($key, 0, $last_dotpos);
				$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
			}
		}
	}

	print($key . PHP_EOL);
}
//V1: https://3v4l.org/QpFHG


$docs = [
	"# This is a doc",
	"# This is too",
	"haha: text",
	"  test:",
	"    Val: r",
	"    Vol:",
	"      New: r",
	"  new:",
	"    go: r",
	"u: text #Lmao ##ROFT"
];
$space = 0;
$key = " ";
$spaces = [];
foreach ($docs as $line) {
	$l = ltrim($line);
	$colon_pos = strpos($l, ':');
	if ($colon_pos === false) continue;
	$spaces[$key] = $space;
	$space = strlen($line) - strlen($l);
	if ($space === 0) {
		$key = mb_substr($l, 0, $colon_pos);
	} else {
		if ($space > $spaces[$key]) {
			$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
		} else {
			while($spaces[$key] >= $space) {
				$last_dotpos = strrpos($key, '.');
				if ($spaces[$key] === $space) {
					$key = mb_substr($key, 0, $last_dotpos);
					$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
					break;
				}
				$key = mb_substr($key, 0, $last_dotpos);
			}
		}
	}

	print($key . PHP_EOL);
}

//V2: https://3v4l.org/hXHVf

$docs = [
	"# This is a doc",
	"# This is too",
	"haha: text",
	"  -test:",
	"    -Val: r",
	"    -Vol:",
	"# This is too",
	"      -New:",
	"  - new:",
	"    - go: r",
	"u: text #Lmao ##ROFT"
];

$space = 0;
$key = "";
$spaces = [];
$nums = [];
foreach ($docs as $line) {
	$l = ltrim($line);
	$colon_pos = strpos($l, ':');
	if ($colon_pos === false) {
		print("I continue by $line  but i have the key: " . $key . PHP_EOL);
		continue;
	}
	$space = strlen($line) - strlen($l);
	if ($space === 0) {
		$key = mb_substr($l, 0, $colon_pos);
	} else if ($spaces[$key] < $space) {
		$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
	} else {
		while($spaces[$key] >= $space) {
			$last_dotpos = strrpos($key, '.');
			if ($spaces[$key] === $space) {
				$key = mb_substr($key, 0, $last_dotpos);
				$key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
				break;
			}
			$key = mb_substr($key, 0, $last_dotpos);
		}
	}

	$spaces[$key] = $space;

	print($key . PHP_EOL);
}

//V3 https://3v4l.org/BFm8R