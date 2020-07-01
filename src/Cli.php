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

        $firstStringValue = saveBooleanString($firstFileJSONData[$key] ?? null);
        $secondStringValue = saveBooleanString($secondFileJSONData[$key] ?? null);

        if (!in_array($key, $firstKeys) && in_array($key, $secondKeys)) {
            $result[] = "  + {$key}: {$secondStringValue}";
        } elseif (in_array($key, $firstKeys) && !in_array($key, $secondKeys)) {
            $result[] = "  - {$key}: {$firstStringValue}";
        } else {
            if ($firstStringValue !== $secondStringValue) {
                $result[] = "  + {$key}: {$secondStringValue}";
                $result[] = "  - {$key}: {$firstStringValue}";
            } else {
                $result[] = "    {$key}: {$secondStringValue}";
            }
        }
    }
    $stringResult = "{\n" . implode("\n", $result) . "\n}\n";
    print_r($stringResult);
}

function saveBooleanString($item)
{
    if (is_bool($item)) {
        return $item ? 'true' : 'false';
    }
    return $item;
}