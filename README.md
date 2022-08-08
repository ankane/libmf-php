# LIBMF PHP

[LIBMF](https://github.com/cjlin1/libmf) - large-scale sparse matrix factorization - for PHP

[![Build Status](https://github.com/ankane/libmf-php/workflows/build/badge.svg?branch=master)](https://github.com/ankane/libmf-php/actions)

## Installation

Run:

```sh
composer require ankane/libmf
```

## Getting Started

Prep your data in the format `rowIndex, columnIndex, value`

```php
$data = new Libmf\Matrix();
$data->push(0, 0, 5.0);
$data->push(0, 2, 3.5);
$data->push(1, 1, 4.0);
```

Create a model

```php
$model = new Libmf\Model();
$model->fit($data);
```

Make predictions

```php
$model->predict($rowIndex, $columnIndex);
```

Get the latent factors (these approximate the training matrix)

```php
$model->p();
$model->q();
```

Get the bias (average of all elements in the training matrix)

```php
$model->bias();
```

Save the model to a file

```php
$model->save('model.txt');
```

Load the model from a file

```php
$model = Libmf\Model::load('model.txt');
```

Pass a validation set

```php
$model->fit($data, $validSet);
```

## Cross-Validation

Perform cross-validation

```php
$model->cv($data);
```

Specify the number of folds

```php
$model->cv($data, 5);
```

## Parameters

Pass parameters - default values below

```php
new Libmf\Model(
    loss: Loss::RealL2,     // loss function
    factors: 8,             // number of latent factors
    threads: 12,            // number of threads used
    bins: 25,               // number of bins
    iterations: 20,         // number of iterations
    lambdaP1: 0,            // coefficient of L1-norm regularization on P
    lambdaP2: 0.1,          // coefficient of L2-norm regularization on P
    lambdaQ1: 0,            // coefficient of L1-norm regularization on Q
    lambdaQ2: 0.1,          // coefficient of L2-norm regularization on Q
    learningRate: 0.1,      // learning rate
    alpha: 1,               // importance of negative entries
    c: 0.0001,              // desired value of negative entries
    nmf: false,             // perform non-negative MF (NMF)
    quiet: false            // no outputs to stdout
);
```

### Loss Functions

For real-valued matrix factorization

- `Loss::RealL2` - squared error (L2-norm)
- `Loss::RealL1` - absolute error (L1-norm)
- `Loss::RealKL` - generalized KL-divergence

For binary matrix factorization

- `Loss::BinaryLog` - logarithmic error
- `Loss::BinaryL2` - squared hinge loss
- `Loss::BinaryL1` - hinge loss

For one-class matrix factorization

- `Loss::OneClassRow` - row-oriented pair-wise logarithmic loss
- `Loss::OneClassCol` - column-oriented pair-wise logarithmic loss
- `Loss::OneClassL2` - squared error (L2-norm)

## Metrics

Calculate RMSE (for real-valued MF)

```php
$model->rmse($data);
```

Calculate MAE (for real-valued MF)

```php
$model->mae($data);
```

Calculate generalized KL-divergence (for non-negative real-valued MF)

```php
$model->gkl($data);
```

Calculate logarithmic loss (for binary MF)

```php
$model->logloss($data);
```

Calculate accuracy (for binary MF)

```php
$model->accuracy($data);
```

Calculate MPR (for one-class MF)

```php
$model->mpr($data, $transpose);
```

Calculate AUC (for one-class MF)

```php
$model->auc($data, $transpose);
```

## Example

Download the [MovieLens 100K dataset](https://grouplens.org/datasets/movielens/100k/) and use:

```php
$trainSet = new Libmf\Matrix();
$validSet = new Libmf\Matrix();

if (($handle = fopen('u.data', 'r')) !== false) {
    $i = 0;
    while (($row = fgetcsv($handle, separator: "\t")) !== false) {
        $data = $i < 80000 ? $trainSet : $validSet;
        $data->push($row[0], $row[1], $row[2]);
        $i++;
    }
    fclose($handle);
}

$model = new Libmf\Model(factors: 20);
$model->fit($trainSet, $validSet);

echo $model->rmse($validSet), "\n";
```

## Resources

- [LIBMF: A Library for Parallel Matrix Factorization in Shared-memory Systems](https://www.csie.ntu.edu.tw/~cjlin/papers/libmf/libmf_open_source.pdf)

## History

View the [changelog](https://github.com/ankane/libmf-php/blob/master/CHANGELOG.md)

## Contributing

Everyone is encouraged to help improve this project. Here are a few ways you can help:

- [Report bugs](https://github.com/ankane/libmf-php/issues)
- Fix bugs and [submit pull requests](https://github.com/ankane/libmf-php/pulls)
- Write, clarify, or fix documentation
- Suggest or add new features

To get started with development:

```sh
git clone https://github.com/ankane/libmf-php.git
cd libmf-php
composer install
composer test
```
