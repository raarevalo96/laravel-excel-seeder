<?php


namespace bfinlay\SpreadsheetSeeder;

use bfinlay\SpreadsheetSeeder\Readers\PhpSpreadsheet\FileIterator;
use bfinlay\SpreadsheetSeeder\Readers\PhpSpreadsheet\SourceChunk;
use bfinlay\SpreadsheetSeeder\Readers\PhpSpreadsheet\SourceFile;
use bfinlay\SpreadsheetSeeder\Readers\PhpSpreadsheet\SourceSheet;
use bfinlay\SpreadsheetSeeder\Writers\Database\DestinationTable;
use bfinlay\SpreadsheetSeeder\Writers\Markdown\TextOutputTable;
use bfinlay\SpreadsheetSeeder\Writers\Markdown\TextOutputWriter;
use Illuminate\Support\Facades\DB;

class SpreadsheetSeederMediator
{
    /**
     * @var SpreadsheetSeeder
     */
    private $seeder;

    /**
     * @var SpreadsheetSeederSettings
     */
    private $settings;

    /**
     * @var SourceFile
     */
    private $sourceFile;

    /**
     * @var SourceSheet
     */
    private $sourceSheet;

    /**
     * @var SourceChunk
     */
    private $sourceChunk;

    /**
     * @var DestinationTable
     */
    private $seedTable;

    /**
     * @var string[]
     */
    public $tablesSeeded = [];

    /**
     * @var string[][]
     */
    private $rows = [];

    /**
     * @var string[][]
     */
    private $rawRows = [];

    /**
     * @var \SplFileObject
     */
    private $textOutputFile;

    /**
     * @var TextOutputTable
     */
    private $textOutputTable;

    /**
     * @var int
     */
    private $resultCount = 0;
    private $count = 0;
    private $total = 0;

    public function __construct(SpreadsheetSeeder $seeder)
    {
        $this->seeder = $seeder;
        $this->settings = resolve(SpreadsheetSeederSettings::class);
    }

    /**
     * Run the class
     *
     * @return void
     */
    public function run()
    {
        // Prevent Laravel Framework memory leaks per https://github.com/laravel/framework/issues/30012
        DB::connection()->disableQueryLog();
        DB::connection()->unsetEventDispatcher();

        SeederMemoryHelper::memoryLog(__METHOD__ . '::' . __LINE__ . ' ' . 'start');
        $fileIterator = new FileIterator();
        if (!$fileIterator->count()) {
            $this->seeder->console('No spreadsheet file given', 'error');
            return;
        }

        foreach ($fileIterator as $this->sourceFile) {
            SeederMemoryHelper::memoryLog(__METHOD__ . '::' . __LINE__ . ' ' . 'file');
            $this->seed();
        }

        $this->seeder->tablesSeeded = $this->tablesSeeded;
    }

    public function seed()
    {
        SeederMemoryHelper::memoryLog(__METHOD__ . '::' . __LINE__ . ' ' . 'seed');
        $textOutputWriter = new TextOutputWriter($this->sourceFile);

        foreach ($this->sourceFile as $this->sourceSheet) {
            $m = SeederMemoryHelper::measurements();
            $this->seeder->console("File: " . $this->sourceFile->getFilename() . " Sheet: " . $this->sourceSheet->getTitle() . " Row: " . $this->sourceSheet->key() . " " . $m["memory"] . " " . $m["time"], "info");
            SeederMemoryHelper::memoryLog("File: " . $this->sourceFile->getFilename() . " Sheet: " . $this->sourceSheet->getTitle());

            $this->checkTable();
            $textOutputWriter->openSheet($this->sourceSheet);

            $chunkCount = 1;
            foreach ($this->sourceSheet as $this->sourceChunk) {
                if ($chunkCount > 1) {
                    $m = SeederMemoryHelper::measurements();
                    SeederMemoryHelper::memoryLog("File: " . $this->sourceFile->getFilename() . " Sheet: " . $this->sourceSheet->getTitle() . " Chunk: " . $this->sourceSheet->key());
                    $this->seeder->console("File: " . $this->sourceFile->getFilename() . " Sheet: " . $this->sourceSheet->getTitle() . " Row: " . $this->sourceSheet->key() . " " . $m["memory"] . " " . $m["time"], "info");
                }

                $this->processRows();
                $this->insertRows();

                $textOutputWriter->saveChunk($this->rawRows);

                if ($this->exceedsLimit()) break;

                $this->clearChunkMemory();
                SeederMemoryHelper::memoryLog(__METHOD__ . '::' . __LINE__ . ' ' . 'processed');
                $chunkCount++;
            }

            $textOutputWriter->closeSheet();
            $this->outputResults();
        }
    }

    private function clearChunkMemory() {
        $this->rows = [];
        $this->rawRows = [];
    }

    private function checkColumns()
    {
        if ($this->sourceSheet->isCsv() && count($this->sourceSheet->getHeader()->toArray()) == 1)
            $this->seeder->console('Found only one column in header.  Maybe the delimiter set for the CSV is incorrect: [' . $this->sourceFile->getDelimiter() . ']');
    }

    private function checkTable()
    {
        $tableName = isset($this->settings->tablename) ? $this->settings->tablename : $this->sourceSheet->getTableName();
        $this->seedTable = new DestinationTable($tableName);


        if (!$this->seedTable->exists()) {
            $this->seeder->console('Table "' . $tableName . '" could not be found in database', 'error');
            return;
        }

        $this->tablesSeeded[] = $tableName;
    }

    /**
     * Process each row of the data source
     *
     * @return void
     */
    private function processRows()
    {
        foreach ($this->sourceChunk as $row) {
            $this->total++;

            if (!$row->isValid()) continue;

            $this->rows[] = $row->toArray();
            $this->rawRows[] = $row->rawRow();

            $this->count++;
            $this->resultCount++;

            if ($this->exceedsLimit()) break;
        }
    }

    /**
     * Insert rows into table
     *
     * @return void
     */
    private function insertRows()
    {
        if (empty($this->rows)) return;

        try {
            $this->seedTable->insertRows($this->rows);

            $this->rows = [];

            $this->count = 0;
        } catch (\Exception $e) {
            $this->seeder->console('Rows of the file "' . $this->sourceFile->getFilename() . '" sheet "' . $this->sourceSheet->getTitle() . '" has failed to insert in table "' . $this->seedTable->getName() . '": ' . $e->getMessage(), 'error');

            die();
        }
    }

    /**
     * Output the result of seeding
     *
     * @return void
     */
    private function outputResults()
    {
        $this->seeder->console($this->resultCount . ' of ' . $this->total . ' rows has been seeded in table "' . $this->seedTable->getName() . '"');
        $this->total = 0;
        $this->resultCount = 0;
    }

    private function exceedsLimit()
    {
        return isset($this->settings->limit) && $this->total >= $this->settings->limit;
    }
}
