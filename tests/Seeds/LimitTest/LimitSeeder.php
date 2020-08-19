<?php


namespace bfinlay\SpreadsheetSeeder\Tests\Seeds\LimitTest;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeeder;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\LargeNumberOfRowsTest\FakeNamesXlsxSeeder;

class LimitSeeder extends FakeNamesXlsxSeeder
{
    public function run()
    {
        $this->limit = 5000;

        parent::run();
    }
}