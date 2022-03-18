<?php
$docs = [
	"# This is a doc",
	"# This is too",
	"haha: text",
	"  - test:",
	"    - Val: r",
	"  - new:",
	"    -go: r",
	"u: text #Lmao ##ROFT"
];
$e = 0;
$key = "";
$sub_key = "";
foreach ($docs as $line) {
	$l = ltrim($line);
	$colon_pos = strpos($l, ':');
	if ($colon_pos === false) {
		continue;
	}

	$space = strlen($line) - strlen($l);
	if ($space === 0) {
		$key = mb_substr($l, 0, $colon_pos);
		print($sub_key . "     =>      " . $key . PHP_EOL);
		$sub_key = $key;
	}
	if ($space > $e) {
		print($sub_key . "    =>    " . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos)) . PHP_EOL);
		$sub_key .= "." . str_replace([' ', '-'], '', mb_substr($l, 0, $colon_pos));
		$e = $space;
	} elseif ($space < $e) {
		$last_dotpos = strrpos($sub_key, '.');
		print($sub_key . "      =>       " . mb_substr($sub_key, 0, $last_dotpos) . PHP_EOL);
		$sub_key = mb_substr($sub_key, 0, $last_dotpos);
		$e = $space;
	}
	print($sub_key . PHP_EOL);
}

//https://3v4l.org/kOrZZ