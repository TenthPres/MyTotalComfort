<?php

const PHPDOC_PHAR_URL = "https://github.com/phpDocumentor/phpDocumentor/releases/download/v2.9.1/phpDocumentor.phar";


file_put_contents("phpdoc.phar", fopen(PHPDOC_PHAR_URL, 'r'));

exec("php phpdoc.phar -d src -t docs/ --template=\"xml\"");

array_map('unlink', glob('docs/*.md'));

//echo exec("" . __DIR__ . "/vendor/bin/phpdoc -d src -t docs/ --template=\"xml\"");

$argv[1] = "docs/structure.xml";
$argv[2] = "docs";
$argv[3] = "--lt";
$argv[4] = "%c";
$argv[5] = "--index";
$argv[6] = "_Sidebar.md";

include "vendor/evert/phpdoc-md/bin/phpdocmd";

echo exec("cd " . __DIR__ . "/docs && git add *.md && git commit -am \"Auto-Updated Documentation\" && git push");