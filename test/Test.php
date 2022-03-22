<?php

$yaml = <<<YAML
# Hi
--- # hello
hi: hello
...
YAML;

$data = yaml_parse($yaml);
$data = ['# Footer comment', '#New stuff'];
var_dump($data);
