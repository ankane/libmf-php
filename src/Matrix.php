<?php

namespace Libmf;

class Matrix
{
    // TODO make private in 0.2.0
    public $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function push($rowIndex, $columnIndex, $value)
    {
        $this->data[] = [$rowIndex, $columnIndex, $value];
    }
}
