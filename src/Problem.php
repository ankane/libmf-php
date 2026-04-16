<?php

namespace Libmf;

class Problem
{
    private $nodes;
    public $prob;

    public function __construct($data)
    {
        $ffi = FFI::instance();

        $nnz = count($data->data);
        if ($nnz == 0) {
            throw new Exception('No data');
        }

        $prob = $ffi->new('struct mf_problem');
        $nodes = $ffi->new("struct mf_node[$nnz]");
        $intMax = 2**31 - 1;
        $umax = -1;
        $vmax = -1;
        for ($i = 0; $i < $nnz; $i++) {
            $row = $data->data[$i];

            $u = $row[0];
            if ($u < 0 || $u >= $intMax) {
                throw new Exception('Invalid row index');
            }

            $v = $row[1];
            if ($v < 0 || $v >= $intMax) {
                throw new Exception('Invalid column index');
            }

            if ($u > $umax) {
                $umax = $u;
            }
            if ($v > $vmax) {
                $vmax = $v;
            }

            $node = $nodes[$i];
            $node->u = $u;
            $node->v = $v;
            $node->r = $row[2];
        }

        $prob->m = $umax + 1;
        $prob->n = $vmax + 1;
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
