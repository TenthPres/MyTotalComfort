<?php
echo exec("phpdoc -d src/ -t docs/ --template=\"xml\"");
$argv[1] = "docs/structure.xml";
$argv[2] = "docs";
include "vendor/evert/phpdoc-md/bin/phpdocmd";
echo exec("cd " . __DIR__ . "/docs && git add *.md && git commit -am \"Auto-Updated Documentation\" && git push");