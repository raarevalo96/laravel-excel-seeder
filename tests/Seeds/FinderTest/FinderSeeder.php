<?php


namespace bfinlay\SpreadsheetSeeder\Tests\Seeds\FinderTest;


use bfinlay\SpreadsheetSeeder\SpreadsheetSeeder;
use Symfony\Component\Finder\Finder;

class FinderSeeder extends SpreadsheetSeeder
{
    public function run()
    {
        $this->textOutput = false;
        $this->batchInsertSize = 5000;
        $this->readChunkSize = 5000;
        parent::run();
    }

}