<?php

use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testWorks()
    {
        $model = new Libmf\Model(quiet: true);
        $model->fit($this->generateData());

        $this->assertEquals(5, $model->rows());
        $this->assertEquals(4, $model->columns());
        $this->assertEquals(8, $model->factors());
        $this->assertIsScalar($model->bias());
        $this->assertCount($model->rows(), $model->p());
        $this->assertCount($model->factors(), $model->p()[0]);
        $this->assertCount($model->columns(), $model->q());
        $this->assertCount($model->factors(), $model->q()[0]);

        $pred = $model->predict(1, 1);
        $path = tempnam(sys_get_temp_dir(), 'model');
        $model->save($path);
        $model = Libmf\Model::load($path);
        $this->assertEqualsWithDelta($pred, $model->predict(1, 1), 0.001);
    }

    public function testValidSet()
    {
        $trainSet = $this->generateData();
        $validSet = $this->generateData();

        $model = new Libmf\Model(quiet: true);
        $this->assertNull($model->fit($trainSet, $validSet));
    }

    public function testCv()
    {
        $model = new Libmf\Model(quiet: true);
        $this->assertIsScalar($model->cv($this->generateData()));
    }

    public function testRealKL()
    {
        $model = new Libmf\Model(quiet: true, loss: Libmf\Loss::RealKL);
        $this->assertNull($model->fit($this->generateData()));
    }

    public function testNoData()
    {
        $this->expectException(Libmf\Exception::class);
        $this->expectExceptionMessage('No data');

        $model = new Libmf\Model();
        $model->fit(new Libmf\Matrix());
    }

    public function testSaveMissing()
    {
        $this->expectException(Libmf\Exception::class);
        $this->expectExceptionMessage('Cannot save model');

        $model = new Libmf\Model(quiet: true);
        $model->fit($this->generateData());
        $model->save('missing/model.txt');
    }

    public function testLoadMissing()
    {
        $this->expectException(Libmf\Exception::class);
        $this->expectExceptionMessage('Cannot open model');

        $model = new Libmf\Model();
        $model->load('missing.txt');
    }

    public function testFitBadParam()
    {
        $this->expectException(Libmf\Exception::class);
        $this->expectExceptionMessage('fit failed');

        $model = new Libmf\Model(factors: 0);
        $model->fit($this->generateData());
    }

    public function testCvBadParam()
    {
        $this->expectException(Libmf\Exception::class);
        $this->expectExceptionMessage('cv failed');

        $model = new Libmf\Model(factors: 0);
        $model->cv($this->generateData());
    }

    private function generateData()
    {
        $data = new Libmf\Matrix();
        for ($i = 0; $i < 20; $i++) {
            $data->push($i % 5, $i % 4, rand(1, 5));
        }
        return $data;
    }
}
