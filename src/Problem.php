<?php

namespace Libmf;

class Problem
{
    public function __construct($data)
    {
        $ffi = FFI::instance();

        $nnz = count($data->data);
        if ($nnz == 0) {
            throw new Exception("No data");
        }

        $prob = $ffi->new('struct mf_problem');
        $nodes = $ffi->new("struct mf_node[$nnz]");

        $m = -1;
        $n = -1;
        for ($i = 0; $i < $nnz; $i++) {
            $row = $data->data[$i];
            $node = $nodes[$i];
            $node->u = $row[0];
            $node->v = $row[1];
            $node->r = $row[2];

            if ($node->u > $m) {
                $m = $node->u;
            }
            if ($node->v > $n) {
                $n = $node->v;
            }
        }

        $prob->n = $n + 1;
        $prob->m = $m + 1;
        $prob->nnz = $nnz;
        $prob->R = \FFI::addr($nodes[0]);

        // keep reference
        $this->nodes = $nodes;
        $this->prob = $prob;
    }

    public function addr()
    {
        return \FFI::addr($this->prob);
    }
}
