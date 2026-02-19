<?php

namespace Libmf;

class Pointer
{
    public $ptr;
    private $free;

    public function __construct($ptr, $free)
    {
        $this->ptr = $ptr;
        $this->free = $free;
    }

    public function __destruct()
    {
        ($this->free)($this->ptr);
    }
}
