<?php


namespace bfinlay\SpreadsheetSeeder;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Facades\DB;

class DestinationTable
{
    private $name;

    /**
     * @var bool
     */
    private $exists;

    /**
     * @var SpreadsheetSeederSettings
     */
    private $settings;

    /**
     * @var string[]
     */
    private $columns;

    /**
     * @var array
     */
    private $rows;

    /**
     * 
     * See methods in vendor/doctrine/dbal/lib/Doctrine/DBAL/Schema/Column.php
     * 
     * @var Column
     */
    private $doctrineColumns;

    public function __construct($name, SpreadsheetSeederSettings $settings)
    {
        $this->name = $name;
        $this->settings = resolve(SpreadsheetSeederSettings::class);

        if ($this->exists() && $this->settings->truncate) $this->truncate();
    }

    public function getName() {
        return $this->name;
    }

    public function exists() {
        if (isset($this->exists)) return $this->exists;
        $this->exists =  DB::getSchemaBuilder()->hasTable( $this->name );

        return $this->exists;
    }

    public function truncate( $foreignKeys = TRUE ) {
        if( ! $foreignKeys ) DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        DB::table( $this->name )->truncate();

        if( ! $foreignKeys ) DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    }

    private function loadColumns() {
        if (! isset($this->columns)) {
            $this->columns = DB::getSchemaBuilder()->getColumnListing( $this->name );
            $this->doctrineColumns = DB::getSchemaBuilder()->getConnection()->getDoctrineSchemaManager()->listTableColumns($this->name);
        }
    }

    public function getColumns() {
        $this->loadColumns();

        return $this->columns;
    }

    public function getColumnType($name) {
        $this->loadColumns();

        return $this->doctrineColumns[$name]->getType()->getName();
    }

    public function columnExists($columnName) {
        $this->loadColumns();

        return in_array($columnName, $this->columns);
    }

    private function transformNullCellValue($columnName, $value) {
        if (is_null($value)) {
            $value = $this->defaultValue($columnName);
        }
        return $value;
    }

    private function checkRows($rows) {
        foreach ($rows as $row) {
            $tableRow = [];
            foreach ($row as $column => $value) {
                if ($this->columnExists($column)) $tableRow[$column] = $this->transformNullCellValue($column, $value);
            }
            $this->rows[] = $tableRow;
        }
    }

    public function insertRows($rows) {
        if( empty($rows) ) return;

        $this->checkRows($rows);

        DB::table( $this->name )->insert( $this->rows );
    }

    public function defaultValue($column) {
        $this->loadColumns();

        $c = $this->doctrineColumns[$column];

        // return default value for column if set
        if ($c->getDefault()) return $c->getDefault();

        // if column is auto-incrementing return null and let database set the value
        if ($c->getAutoincrement()) return null;

        // if column accepts null values, return null
        if (! $c->getNotnull()) return null;

        // if column is numeric, return 0
        $doctrineNumericValues = ['smallint', 'integer', 'bigint', 'decimal', 'float'];
        if (in_array($c->getType()->getName(), $doctrineNumericValues)) return 0;
        
        // if column is date or time type return 
        $doctrineDateValues = ['date', 'date_immutable', 'datetime', 'datetime_immutable', 'datetimez', 'datetimez_immutable', 'time', 'time_immutable', 'dateinterval'];
        if (in_array($c->getType()->getName(), $doctrineDateValues)) {
            if ($this->settings->timestamps) return date('Y-m-d H:i:s');
            else return 0;
        } 
            
        // if column is boolean return false
        if ($c->getType()->getName() == "boolean") return false;
        
        // else return empty string
        return "";
    }
}