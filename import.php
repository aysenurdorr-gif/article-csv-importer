<?php

require_once __DIR__ . '/src/ArticleImporter.php';

if ($argc !== 3) {
    echo "Usage: php import.php artikel.csv ausgabe.json\n";
    exit(1);
}

$inputFile = $argv[1];
$outputFile = $argv[2];

try {
    $importer = new ArticleImporter($inputFile, $outputFile);
    $importer->import();
} catch (RuntimeException | JsonException $error) {
    echo "Error: " . $error->getMessage() . "\n";
    exit(1);
}