<?php


namespace bfinlay\SpreadsheetSeeder\Writers\Markdown;

use bfinlay\SpreadsheetSeeder\Readers\PhpSpreadsheet\SourceFile;
use bfinlay\SpreadsheetSeeder\Readers\PhpSpreadsheet\SourceSheet;
use bfinlay\SpreadsheetSeeder\SpreadsheetSeederSettings;

/**
 * Class TextOutputWriter
 * @package bfinlay\SpreadsheetSeeder
 *
 * Loosely based on PhpSpreadsheet's "Writer" IWriter interface.  PhpSpreadsheet's Writer does not have a means for
 * writing chunks, so this class has a different interface.  There are four public methods:
 *
 * 1. public function __construct(SourceFile $sourceFile)
 *    This corresponds to Writer::__construct($spreadsheet).
 *    In laravel-excel-seeder, SourceFile corresponds to PhpSpreadsheet $spreadsheet
 *
 * 2. public function openSheet(SourceSheet $sourceSheet)
 *    This corresponds to Writer->setSheetIndex(index).
 *    instead of passing the index, the sourceSheet is passed in.
 *    The method is named 'openSheet' instead of setSheet to correspond to closeSheet method, which does not exist in writer interface
 *    In laravel-excel-seeder, SourceSheet corresponds to the worksheet object that would be returned from sheet index
 *
 * 3. public function saveChunk($rows)
 *    This corresponds to part of Writer->save($outputFile).
 *    This is the missing functionality from the PhpSpreadsheet api.  This is used to save a chunk before the chunk is freed.
 *
 * 4. public function closeSheet()
 *    This corresponds to part of Writer->save($outputFile).
 *    The PhpSpreadsheet api does not support writing chunks, so closing the output file occurs at the end of the save call.
 *
 *  This is kind of a container for managing TextOutputTable.
 *  TextOutputTable corresponds better to the writer, which still needs write chunk
 *
 */
class TextOutputWriter
{
    /**
     * @var SourceFile
     */
    private $sourceFile;

    /**
     * @var TextOutputTable
     */
    private $table;

    /**
     * @var string
     */
    private $_pathName;

    private $activeSheetName;

    public function __construct(SourceFile $sourceFile, SpreadsheetSeederSettings $settings = null)
    {
        $this->sourceFile = $sourceFile;
        $this->settings = resolve(SpreadsheetSeederSettings::class);

        if (!$this->settings->textOutput) return;

        $this->createTextOutputPath();
    }

    public function openSheet(SourceSheet $sourceSheet)
    {
        if (!$this->settings->textOutput) return;

        if (! $this->isSheetActive( $sourceSheet->getTableName() )) {
            $this->closeSheet();
            $this->table = $this->createTextOutputTable($sourceSheet);
        }
    }

    public function saveChunk($rows)
    {
        if (!$this->settings->textOutput) return;

        $this->table->writeRows($rows);
    }

    public function closeSheet()
    {
        if (!$this->settings->textOutput) return;

        if (isset($this->table)) $this->table->writeFooter();
        unset($this->table);
    }

    private function isSheetActive($name)
    {
        return isset($this->activeSheetName) && $this->activeSheetName == $name;
    }

    private function pathName()
    {
        if (isset($this->_pathName)) return $this->_pathName;

        $this->_pathName = '';
        $path_parts = pathinfo($this->sourceFile->getPathname());
        if (strlen($path_parts['dirname']) > 0) $this->_pathName = $path_parts['dirname'] . '/';
        $this->_pathName = $this->_pathName . $path_parts['filename'];
        return $this->_pathName;
    }

    private function createTextOutputPath()
    {
        $mkdirResult = false;
        if (!(is_dir($this->pathName()))) {
            $mkdirResult = mkdir($this->pathName(), 0777, true);
        }

        array_map('unlink', glob($this->pathName() . "/*"));
    }

    private function createTextOutputFile($tableName)
    {
        $filename = $this->pathName() . '/' . $tableName . '.' . $this->settings->textOutputFileExtension;

        return new \SplFileObject($filename, 'w');
    }

    private function createTextOutputTable(SourceSheet $sourceSheet)
    {
        return new TextOutputTable(
            $this->createTextOutputFile($sourceSheet->getTableName()),
            $sourceSheet->getTableName(),
            $sourceSheet->getHeader()->rawColumns()
        );
    }
}