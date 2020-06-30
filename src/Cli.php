<?php
namespace Gendiff\Cli;

use Docopt;

function run()
{
    $doc = <<<DOC
Generate diff

Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help                     Show this screen
  -v --version                  Show version
  --format <fmt>                Report format [default: pretty]
DOC;
    $args = Docopt::handle($doc);
    $firstFile = $args['<firstFile>'];
    $secondFile = $args['<secondFile>'];

    $firstFileData = file_get_contents($firstFile);
    $secondFileData = file_get_contents($secondFile);

    $firstFileJSONData = json_decode($firstFileData, true);
    $secondFileJSONData = json_decode($secondFileData, true);

    $firstKeys = array_keys($firstFileJSONData);
    $secondKeys = array_keys($secondFileJSONData);
    $unitedKeys = array_values(array_unique(array_merge($firstKeys, $secondKeys)));
    $result = [];

    foreach ($unitedKeys as $key) {
        if (!in_array($key, $firstKeys) && in_array($key, $secondKeys)) {
            $result[] = "  + {$key}: {$secondFileJSONData[$key]}";
        } elseif (in_array($key, $firstKeys) && !in_array($key, $secondKeys)) {
            $result[] = "  - {$key}: {$firstFileJSONData[$key]}";
        } else {
            if ($firstFileJSONData[$key] !== $secondFileJSONData[$key]) {
                $result[] = "  + {$key}: {$secondFileJSONData[$key]}";
                $result[] = "  - {$key}: {$firstFileJSONData[$key]}";
            } else {
                $result[] = "    {$key}: {$secondFileJSONData[$key]}";
            }
        }

    }
    $stringResult = "{\n" . implode("\n", $result) . "\n}\n";
    print_r($stringResult);
}