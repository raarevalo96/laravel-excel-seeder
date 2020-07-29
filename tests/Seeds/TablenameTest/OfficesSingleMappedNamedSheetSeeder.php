<?php


namespace bfinlay\SpreadsheetSeeder\Tests\Seeds\TablenameTest;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeeder;

class OfficesSingleMappedNamedSheetSeeder extends SpreadsheetSeeder
{
    public function run()
    {
        // path is relative to base_path which is laravel-excel-seeder/vendor/orchestra/testbench-core/laravel
        $this->file = '/../../../bfinlay/laravel-excel-seeder-test-data/TablenameTest/offices-single-mapped-named-sheet.xlsx';

        $this->textOutput = false;
        $this->worksheetTableMapping = [
            'offices_mapped' => 'offices'
        ];
        parent::run();
    }
}