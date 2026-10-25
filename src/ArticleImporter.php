<?php

class ArticleImporter
{
    private string $inputFile;
    private string $outputFile;
    private array $errors = [];

    public function __construct(string $inputFile, string $outputFile)
    {
        $this->inputFile = $inputFile;
        $this->outputFile = $outputFile;
    }

    public function import(): void
    {
        if (!is_file($this->inputFile)) {
            throw new RuntimeException('Input file not found.');
        }

        $errorFile = $this->outputFile . '.errors.json';

        $fileHandle = fopen($this->inputFile, 'r');

        if ($fileHandle === false) {
            throw new RuntimeException('Input file could not be opened.');
        }

        $articles = [];
        
        $this->errors = [];
        $readCount = 0;

        // The header is line 1.
        // Each CSV record is assumed to occupy one physical line.
        $lineNumber = 1;

        try {
            $header = fgetcsv($fileHandle, 0, ';', '"', '');

            $expectedHeader = [
                'sku', 'name', 'preis', 'bestand', 'kategorie', 'ean',
            ];

            if ($header !== $expectedHeader) {
                throw new RuntimeException('CSV header is invalid.');
            }

            while (
                ($row = fgetcsv($fileHandle, 0, ';', '"', '')) !== false
            ) {
                $lineNumber++;
                $readCount++;

                // Check the column count before accessing array values.
                if (count($row) !== 6) {
                    $this->addError(
                        $lineNumber,
                        'Incorrect number of columns.'
                    );
                    continue;
                }

                $article = [
                    'sku' => $row[0],
                    'name' => $row[1],
                    'preis' => $row[2],
                    'bestand' => $row[3],
                    'kategorie' => $row[4],
                    'ean' => $row[5],
                ];

                $article = $this->validateArticle($article, $lineNumber);

                // Skip this record and continue with the next CSV record.
                if ($article === null) {
                    continue;
                }

                // The last valid record with the same SKU wins.
                $articles[$article['sku']] = $article;
            }

            if (!feof($fileHandle)) {
                throw new RuntimeException('Input file could not be read.');
            }
        } finally {
            fclose($fileHandle);
        }

        ksort($articles, SORT_STRING);

        // Remove outer SKU keys to produce a JSON array.
        $this->writeJson(
            $this->outputFile,
            array_values($articles)
        );

        $this->writeJson($errorFile, $this->errors);

        $importedCount = count($articles);

        // Includes invalid records and replaced duplicates.
        $skippedCount = $readCount - $importedCount;

        echo "Read: $readCount\n";
        echo "Imported: $importedCount\n";
        echo "Skipped: $skippedCount\n";
        echo "Output saved to: {$this->outputFile}\n";
        echo "Error log saved to: $errorFile\n";
    }

    private function validateArticle(array $article, int $lineNumber): ?array
    {
        $article['sku'] = trim($article['sku']);

        if ($article['sku'] === '') {
            $this->addError($lineNumber, 'SKU is missing.');
            return null;
        }

        // Normalize the decimal separator and remove euro signs.
        $price = trim($article['preis']);
        $price = str_replace('€', '', $price);
        $price = trim($price);
        $price = str_replace(',', '.', $price);

        if (!is_numeric($price)) {
            $this->addError(
                $lineNumber,
                'Price is invalid or missing.'
            );
            return null;
        }

        $price = (float) $price;

        if (!is_finite($price) || $price < 0) {
            $this->addError($lineNumber, 'Price is invalid.');
            return null;
        }

        $article['preis'] = round($price, 2);

        $stock = trim($article['bestand']);

        // An empty stock field means zero.
        if ($stock === '') {
            $stock = '0';
        }

        $validatedStock = filter_var($stock, FILTER_VALIDATE_INT);

        // A stock value of 0 is valid, so compare strictly with false.
        if ($validatedStock === false || $validatedStock < 0) {
            $this->addError(
                $lineNumber,
                'Stock must be a non-negative integer.'
            );
            return null;
        }

        $article['bestand'] = $validatedStock;

        return $article;
    }

    private function addError(int $lineNumber, string $reason): void
    {
        $this->errors[] = [
            'line' => $lineNumber,
            'reason' => $reason,
        ];
    }

    private function writeJson(string $filePath, array $data): void
    {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR
        );

        // Existing output files are overwritten.
        $written = file_put_contents($filePath, $json);

        if ($written === false || $written !== strlen($json)) {
            throw new RuntimeException(
                "File could not be written completely: $filePath"
            );
        }
    }
}