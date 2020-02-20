<?php


namespace bfinlay\SpreadsheetSeeder;


class TextOutputTable
{
    /**
     * @var \SplFileObject
     */
    private $file;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string[]
     */
    private $header;

    /**
     * @var array
     */
    private $rows;

    /**
     * @var int[]
     */
    private $columnWidths;

    /**
     * @var int
     */
    private $columnPadding = 2;

    /**
     * TextOutputTable constructor.
     * @param \SplFileObject $file
     * @param string $tableName
     * @param string[] $header
     * @param array $rows
     */
    public function __construct(\SplFileObject $file, $tableName, $header, $rows)
    {
        $this->file = $file;
        $this->tableName = $tableName;
        $this->header = $header;
        $this->rows = $rows;
    }
    
    public function write() {
        if (! $this->file->isWritable()) throw new Exception('File ' . $this->file->getFilename() . ' is not open for writing.');
        
        $this->columnWidths();
        $this->writeTableName();
        $this->writeTableHeader();
        $this->writeTableRows();
    }

    private function writeTableName() {
        $this->file->fwrite($this->tableName . "\n");
        $border = str_repeat('=', strlen($this->tableName));
        $this->file->fwrite($border . "\n\n");
    }

    private function writeTableHeader() {
        foreach ($this->header as $index => $columnName) {
            $columnHeader = str_pad($columnName, $this->columnWidths[$index] + $this->columnPadding, " ", STR_PAD_BOTH);
            $columnSeperator = ($index > 0) ? $columnSeperator = '|' : $columnSeperator = '';
            $this->file->fwrite($columnSeperator . $columnHeader);
        }
        $this->file->fwrite("\n");

        foreach ($this->header as $index => $columnName) {
            $columnHeader = str_repeat('-', $this->columnWidths[$index] + $this->columnPadding);
            $columnSeperator = ($index > 0) ? $columnSeperator = '+' : $columnSeperator = '';
            $this->file->fwrite($columnSeperator . $columnHeader);
        }
        $this->file->fwrite("\n");
    }

    private function writeTableRows() {
        foreach ($this->rows as $row) {
            foreach ($row as $index => $value) {
                $valueCell = str_pad($value, $this->columnWidths[$index]);
                $columnSeperator = ($index > 0) ? $columnSeperator = '|' : $columnSeperator = '';
                $this->file->fwrite($columnSeperator . ' ' . $valueCell . ' ');
            }
            $this->file->fwrite("\n");
        }
        $this->file->fwrite('(' . count($this->rows) . " rows)\n\n");
    }

    private function columnWidths() {
        foreach ($this->header as $index => $columnName) {
            $this->columnWidths[$index] = strlen($columnName);
        }

        foreach ($this->rows as $row) {
            foreach ($row as $index => $value) {
                if (strlen($value) > $this->columnWidths[$index])
                    $this->columnWidths[$index] = strlen($value);
            }
        }
    }
}