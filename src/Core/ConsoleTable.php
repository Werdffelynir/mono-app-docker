<?php

namespace Lib\Core;

class ConsoleTable {
    private string $ascii;
    private int $width = 0;
    private const COMPENSATION = 3;
    private array $columns;
    private array $data;
    private array $description = [
        'columns' => 0,
    ];
    public function __construct($data, $columns = [], $numeric = false)
    {
        if ($numeric) {
            $i = 0;
            foreach ($data as $index => $value) {
                $data[$index] = ['num' => ++$i] + $data[$index];
            }
        }

        $this->data = $data;
        $this->ascii = "";
        $this->width = $this->calcWidth();
        $this->columns = empty($columns) ? array_keys($data[0]) : $columns;
    }

    public function draw()
    {
        $this->head();
        $this->row();
        $this->description();

        echo $this->ascii;
    }

    private function head()
    {
        foreach ($this->columns as $name) {
            $width = $this->calcWidth($name);
            $this->column($name, $width);
        }
        $line = $this->hr();
        $this->ascii("\n{$line}");
    }

    private function column($value, $width = null)
    {
        $width = $width ? $width : $this->width;
        $this->ascii(str_pad($value, $width));
    }

    private function row()
    {
        foreach ($this->data as $data) {
            foreach ($data as $key => $value) {
                $width = $this->calcWidth($key);
                $this->column($value, $width);
            }
            $this->description['columns'] ++;
            $this->ascii("\n");
        }
    }


    private function description()
    {
        $description = $this->hr()
            . "Columns: {$this->description['columns']}";
        $this->ascii($description . "\n");
    }

    private function calcWidth($column = null)
    {
        $width = 0;
        foreach ($this->data as $data) {
            foreach ($data as $key => $value) {
                if ($column) {
                    if ($key === $column) {
                        $len = strlen($value) + self::COMPENSATION;
                        $width = $len > $width ? $len : $width;
                    }
                } else {
                    $len = strlen($value) + self::COMPENSATION;
                    $width = $len > $width ? $len : $width;
                }
            }
        }
        return $width;
    }

    private function ascii(string $string)
    {
        $this->ascii .= $string;
    }

    private function br()
    {
        return "\n";
    }

    private function hr()
    {
        $width = ($this->width + self::COMPENSATION) * count($this->columns) / 2;
        return str_pad('', $width, '-') . "\n";
    }
}