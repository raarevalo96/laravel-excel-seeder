<?php


namespace bfinlay\SpreadsheetSeeder;


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use SplFileInfo;

class SourceFile implements \Iterator
{
    /**
     * @var SplFileInfo
     */
    private $file;

    /**
     * @var string
     */
    private $fileType;

    /**
     * @var BaseReader
     */
    private $reader;

    /**
     * @var SpreadsheetSeederSettings
     */
    private $settings;

    /**
     * @var Spreadsheet
     */
    private $spreadsheet;


    private $worksheetIterator;

    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
        $this->settings = resolve(SpreadsheetSeederSettings::class);

        if (!$this->shouldSkip()) $this->worksheetIterator = $this->getWorksheetIterator();
    }

    public function shouldSkip() {
        if (substr($this->file->getFilename(), 0, 1) === "~" ) return true;

        return false;
    }

    public function getWorksheetIterator() {
        if (!isset($this->spreadsheet)) {
            $filename = $this->file->getPathname();
            $this->fileType = IOFactory::identify($filename);
            $this->reader = IOFactory::createReader($this->fileType);
            if ($this->fileType == "Csv" && !empty($this->settings->delimiter)) {
                $this->reader->setDelimiter($this->settings->delimiter);
            }
            $this->spreadsheet = $this->reader->load($filename);
        }
        return $this->spreadsheet->getWorksheetIterator();
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        $sourceSheet = new SourceSheet($this->worksheetIterator->current(), $this->settings);
        $sourceSheet->setFileType($this->fileType);
        if ($this->spreadsheet->getSheetCount() == 1) {
            $sourceSheet->setTableName($this->file->getBasename("." . $this->file->getExtension()));
        }
        return $sourceSheet;
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->worksheetIterator->next();
        if (! $this->valid() ) return;
        $worksheet = $this->worksheetIterator->current();
        // If this worksheet is marked for skipping, recursively call this function for the next sheet
        if( $this->settings->skipper == substr($worksheet->getTitle(), 0, strlen($this->settings->skipper)) ) $this->next();
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->worksheetIterator->key();
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->worksheetIterator->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        return $this->worksheetIterator->rewind();
    }

    public function getFilename() {
        return $this->file->getFilename();
    }
    
    public function getPathname() {
        return $this->file->getPathname();
    }

    public function getDelimiter() {
        return $this->reader->getDelimiter();
    }
}