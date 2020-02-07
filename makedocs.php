<?php

const PHPDOC_PHAR_URL = "https://github.com/phpDocumentor/phpDocumentor/releases/download/v2.9.1/phpDocumentor.phar";
const PHPDOC_PHAR_FILENAME = "phpdoc.phar";

if (!file_exists(PHPDOC_PHAR_FILENAME) || (time() - filemtime(PHPDOC_PHAR_FILENAME) > 86400)) {
    echo "Downloading updated PHPDoc PHAR...";
    file_put_contents(PHPDOC_PHAR_FILENAME, fopen(PHPDOC_PHAR_URL, 'r'));
    echo "    Complete.\n";
}


echo "Running PHPDoc Analysis...";
exec("php " . PHPDOC_PHAR_FILENAME . " -d src -t docs/ --template=\"xml\"");
echo "    Complete\n";


echo "Removing previous documentation files...";
array_map('unlink', glob('docs/*.md'));
echo "    Complete.\n";


echo "Creating Markdown files...";
$argv[1] = "docs/structure.xml";
$argv[2] = "docs";
$argv[3] = "--lt";
$argv[4] = "%c";
$argv[5] = "--index";
$argv[6] = "_Sidebar.md";
include "vendor/evert/phpdoc-md/bin/phpdocmd";
echo "    Complete.\n";


echo "Committing and pushing to Repository...";
echo exec("cd " . __DIR__ . "/docs && git add *.md && git commit -am \"Auto-Updated Documentation\" && git push");
echo "    Complete.\n";
