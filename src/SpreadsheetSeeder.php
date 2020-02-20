<?php

namespace bfinlay\SpreadsheetSeeder;

use DB;
use Illuminate\Database\Seeder;


class SpreadsheetSeeder extends Seeder
{
    /**
     * Settings
     *
     * @var SpreadsheetSeederSettings
     */
    private $settings;

    /**
     * Mediator
     *
     * @var SpreadsheetSeederMediator
     */
    private $mediator;

    /**
     * @var string[]
     */
    public $tablesSeeded;

    public function __construct()
    {
        $this->settings = resolve(SpreadsheetSeederSettings::class);
    }

    /**
     * Run the class
     *
     * @return void
     */
    public function run()
    {
        $this->mediator = new SpreadsheetSeederMediator($this);

        $this->mediator->run();
    }

    public function __set($name, $value) {
        $this->settings->$name = $value;
    }

    /**
     * Logging
     *
     * @param string $message
     * @param string $level
     * @return void
     */
    public function console( $message, $level = FALSE )
    {
        if( $level ) $message = '<'.$level.'>'.$message.'</'.$level.'>';

        $this->command->line( '<comment>SpreadsheetSeeder: </comment>'.$message );
    }
}
