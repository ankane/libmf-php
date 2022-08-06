<?php

namespace Libmf;

class Matrix
{
    public function __construct()
    {
        $this->data = [];
    }

    public function push($rowIndex, $columnIndex, $value)
    {
        $this->data[] = [$rowIndex, $columnIndex, $value];
    }
}
