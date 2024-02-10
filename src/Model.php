<?php

namespace Libmf;

class Model
{
    // TODO make private in 0.2.0
    public $loss;
    public $factors;
    public $threads;
    public $bins;
    public $iterations;
    public $lambdaP1;
    public $lambdaP2;
    public $lambdaQ1;
    public $lambdaQ2;
    public $learningRate;
    public $alpha;
    public $c;
    public $nmf;
    public $quiet;
    public $ffi;
    public $model;

    public function __construct(
        $loss = Loss::RealL2,
        $factors = 8,
        $threads = 12,
        $bins = 25,
        $iterations = 20,
        $lambdaP1 = 0,
        $lambdaP2 = 0.1,
        $lambdaQ1 = 0,
        $lambdaQ2 = 0.1,
        $learningRate = 0.1,
        $alpha = 1,
        $c = 0.0001,
        $nmf = false,
        $quiet = false
    ) {
        $this->loss = $loss;
        $this->factors = $factors;
        $this->threads = $threads;
        $this->bins = $bins;
        $this->iterations = $iterations;
        $this->lambdaP1 = $lambdaP1;
        $this->lambdaP2 = $lambdaP2;
        $this->lambdaQ1 = $lambdaQ1;
        $this->lambdaQ2 = $lambdaQ2;
        $this->learningRate = $learningRate;
        $this->alpha = $alpha;
        $this->c = $c;
        $this->nmf = $nmf;
        $this->quiet = $quiet;

        $this->ffi = FFI::instance();
        $this->model = null;
    }

    public function __destruct()
    {
        $this->destroyModel();
    }

    public function fit($trainSet, $validSet = null)
    {
        $tr = new Problem($trainSet);

        if (is_null($validSet)) {
            $model = $this->ffi->mf_train($tr->addr(), $this->param());
        } else {
            $va = new Problem($validSet);
            $model = $this->ffi->mf_train_with_validation($tr->addr(), $va->addr(), $this->param());
        }

        if (is_null($model)) {
            throw new Exception("fit failed");
        }

        $this->setModel($model);
    }

    public function predict($rowIndex, $columnIndex)
    {
        return $this->ffi->mf_predict($this->model(), $rowIndex, $columnIndex);
    }

    public function cv($data, $folds = 5)
    {
        $prob = new Problem($data);
        // TODO update fork to differentiate between bad parameters and zero error
        $res = $this->ffi->mf_cross_validation($prob->addr(), $folds, $this->param());
        if ($res == 0) {
            throw new Exception("cv failed");
        }
        return $res;
    }

    public function save($path)
    {
        $status = $this->ffi->mf_save_model($this->model(), $path);
        if ($status != 0) {
            throw new Exception("Cannot save model");
        }
    }

    public static function load($path)
    {
        $model = new Model();
        $model->load_model($path);
        return $model;
    }

    private function load_model($path)
    {
        $model = $this->ffi->mf_load_model($path);
        if (is_null($model)) {
            throw new Exception("Cannot open model");
        }
        $this->setModel($model);
    }

    public function rows()
    {
        return $this->model()->m;
    }

    public function columns()
    {
        return $this->model()->n;
    }

    public function factors()
    {
        return $this->model()->k;
    }

    public function bias()
    {
        return $this->model()->b;
    }

    public function p()
    {
        return $this->readFactors($this->model()->P, $this->model()->m);
    }

    public function q()
    {
        return $this->readFactors($this->model()->Q, $this->model()->n);
    }

    public function rmse($data)
    {
        $prob = new Problem($data);
        return $this->ffi->calc_rmse($prob->addr(), $this->model());
    }

    public function mae($data)
    {
        $prob = new Problem($data);
        return $this->ffi->calc_mae($prob->addr(), $this->model());
    }

    public function gkl($data)
    {
        $prob = new Problem($data);
        return $this->ffi->calc_gkl($prob->addr(), $this->model());
    }

    public function logloss($data)
    {
        $prob = new Problem($data);
        return $this->ffi->calc_logloss($prob->addr(), $this->model());
    }

    public function accuracy($data)
    {
        $prob = new Problem($data);
        return $this->ffi->calc_accuracy($prob->addr(), $this->model());
    }

    public function mpr($data, $transpose)
    {
        $prob = new Problem($data);
        return $this->ffi->calc_mpr($prob->addr(), $this->model(), $transpose);
    }

    public function auc($data, $transpose)
    {
        $prob = new Problem($data);
        return $this->ffi->calc_auc($prob->addr(), $this->model(), $transpose);
    }

    private function readFactors($ptr, $rows)
    {
        $factors = [];
        $k = 0;
        for ($i = 0; $i < $rows; $i++) {
            $row = [];
            for ($j = 0; $j < $this->model()->k; $j++) {
                $row[] = $ptr[$k];
                $k++;
            }
            $factors[] = $row;
        }
        return $factors;
    }

    private function model()
    {
        if (is_null($this->model)) {
            throw new Exception('Not fit');
        }
        return $this->model;
    }

    private function setModel($model)
    {
        $this->destroyModel();
        $this->model = $model;
    }

    private function destroyModel()
    {
        if (!is_null($this->model)) {
            $this->ffi->mf_destroy_model(\FFI::addr($this->model));
            $this->model = null;
        }
    }

    private function param()
    {
        $param = $this->ffi->mf_get_default_param();
        $param->fun = $this->loss->value;
        $param->k = $this->factors;
        $param->nr_threads = $this->threads;
        $param->nr_bins = $this->bins;
        $param->nr_iters = $this->iterations;
        $param->lambda_p1 = $this->lambdaP1;
        $param->lambda_q1 = $this->lambdaQ1;
        $param->lambda_p2 = $this->lambdaP2;
        $param->lambda_q2 = $this->lambdaQ2;
        $param->eta = $this->learningRate;
        $param->alpha = $this->alpha;
        $param->c = $this->c;
        $param->do_nmf = $this->nmf;
        $param->quiet = $this->quiet;
        $param->copy_data = false;

        // do_nmf must be true for generalized KL-divergence
        if ($param->fun == 2) {
            $param->do_nmf = true;
        }

        return $param;
    }
}
