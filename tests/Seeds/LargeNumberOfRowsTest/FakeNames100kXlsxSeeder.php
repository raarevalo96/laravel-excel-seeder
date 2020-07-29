<?php


namespace bfinlay\SpreadsheetSeeder\Tests\Seeds\LargeNumberOfRowsTest;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeeder;

class FakeNames100kXlsxSeeder extends SpreadsheetSeeder
{
    public function run()
    {
        // path is relative to base_path which is laravel-excel-seeder/vendor/orchestra/testbench-core/laravel
        $this->file = '/../../../bfinlay/laravel-excel-seeder-test-data/LargeNumberOfRowsTest/fake_names_100k.xlsx';
        $this->tablename = 'fake_names';
        $this->aliases = ['Number' => 'id'];
        $this->textOutput = false;
        $this->batchInsertSize = 12500;
        $this->readChunkSize = 25000;
        parent::run();
    }
}