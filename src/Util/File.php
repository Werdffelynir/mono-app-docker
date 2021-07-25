<?php

namespace Lib\Util;

class File
{
    public static function saveCSVFile(string $file, array $values, array $columns = [])
    {
        $fileResource = fopen($file, 'w');

        if (!empty($columns)) {
            if (count($columns) !== count($values[0])) {
                throw new \RuntimeException("Error length of columns and values items");
            }
            fputcsv($fileResource, $columns);
        }

        foreach ($values as $line) {
            fputcsv($fileResource, $line);
        }

        fclose($fileResource);
    }

    public static function readCSVString(string $string, array $columns = [], bool $firstLineColumns = false)
    {
        $values = [];

        $lines = explode("\n", $string);
        $lines = array_filter($lines, fn($line)=>!empty($line));

        foreach ($lines as $line)
        {
            $start = strpos($line, "{");
            $end = strpos($line, "}");
            if ($start && $end) {
                $jsonString = substr($line, $start,  $end - $start + 1);
                $jsonStringReplace = str_replace('""', '"', $jsonString);
                $jsonStringReplace = str_replace(',', '|', $jsonStringReplace);
                $line = str_replace($jsonString, $jsonStringReplace, $line);
            }
            $values[] = array_map(fn($value)=>trim($value),explode(",", $line));
        }

        if ($firstLineColumns) {
            $columns = array_shift($values);
        }

        if (!empty($columns)) {

            $valuesColumns = [];
            foreach ($values as $value) {
                if (count($columns) !== count($value)) {
                    print_r($value);
                    throw new \Exception("Error length of columns and values items");
                }
                $valuesColumns[] = array_combine($columns, $value);
            }

            return $valuesColumns;
        }

        return $values;
    }

    public static function readCSVFile(string $file, array $columns = [], bool $firstLineColumns = false)
    {
        return self::readCSVString(file_get_contents($file), $columns, $firstLineColumns);
    }

    public static function readFileSize($file, $size, $callback, $breakIteration = 0)
    {
        $i = 0;
        $handle = @fopen($file, "r");

        if ($handle) {
            while (($buffer = fgets($handle, $size)) !== false) {
                $i++;
                $callback($buffer, $i);

                if ($breakIteration && $i >= $breakIteration) break;
            }
            fclose($handle);
        }
    }
}