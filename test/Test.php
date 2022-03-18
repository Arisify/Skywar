<?php

$yaml = <<<YAML
YAML;

$data = yaml_parse($yaml);

print_r($data);
