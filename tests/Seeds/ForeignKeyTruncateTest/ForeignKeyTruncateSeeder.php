<?php


namespace bfinlay\SpreadsheetSeeder\Tests\Seeds\ForeignKeyTruncateTest;


use bfinlay\SpreadsheetSeeder\SpreadsheetSeeder;

class ForeignKeyTruncateSeeder extends SpreadsheetSeeder
{
    public function run()
    {
        // path is relative to base_path which is laravel-excel-seeder/vendor/orchestra/testbench-core/laravel
        $this->file = '/../../../bfinlay/laravel-excel-seeder-test-data/ForeignKeyTruncateTest/ForeignKeyTruncate.xlsx';

        parent::run();
    }
}
