<?php


namespace bfinlay\SpreadsheetSeeder\Tests\Seeds\TablenameTest;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeeder;

class ClassicModelsMultipleMappedNamedSheetSeeder extends SpreadsheetSeeder
{
    public function run()
    {
        // path is relative to base_path which is laravel-excel-seeder/vendor/orchestra/testbench-core/laravel
        $this->file = '/../../../bfinlay/laravel-excel-seeder-test-data/TablenameTest/classic-models-multiple-mapped-named-sheet.xlsx';

        $this->textOutput = false;
        $this->worksheetTableMapping = [
            'employees_mapped' => 'employees',
            'orders_mapped' => 'orders'
        ];
        parent::run();
    }
}