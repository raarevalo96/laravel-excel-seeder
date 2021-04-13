<?php

namespace bfinlay\SpreadsheetSeeder;

use bfinlay\SpreadsheetSeeder\Events\Console;
use bfinlay\SpreadsheetSeeder\Readers\Events\ChunkFinish;
use bfinlay\SpreadsheetSeeder\Readers\Events\ChunkStart;
use bfinlay\SpreadsheetSeeder\Readers\Events\FileFinish;
use bfinlay\SpreadsheetSeeder\Readers\Events\FileSeed;
use bfinlay\SpreadsheetSeeder\Readers\Events\FileStart;
use bfinlay\SpreadsheetSeeder\Readers\Events\SheetFinish;
use bfinlay\SpreadsheetSeeder\Readers\Events\SheetStart;
use bfinlay\SpreadsheetSeeder\Readers\PhpSpreadsheet\SpreadsheetReader;
use bfinlay\SpreadsheetSeeder\Writers\Console\ConsoleWriter;
use bfinlay\SpreadsheetSeeder\Writers\Database\DatabaseWriter;
use bfinlay\SpreadsheetSeeder\Writers\Markdown\MarkdownWriter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Finder\Finder;


class SpreadsheetSeeder extends Seeder
{
    /**
     * Settings
     *
     * @var SpreadsheetSeederSettings
     */
    protected $settings;

    /**
     * @var string[]
     */
    public $tablesSeeded;

    public function __construct(SpreadsheetSeederSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Run the class
     *
     * @return void
     */
    public function run()
    {
        ($consoleWriter = new ConsoleWriter($this->command))->run();
        ($databaseWriter = new DatabaseWriter)->run();
        ($markdownWriter = new MarkdownWriter)->run();
        ($spreadsheetReader = new SpreadsheetReader())->run();

        SeederMemoryHelper::memoryLog(__METHOD__ . '::' . __LINE__ . ' ' . 'start');

        $finder = $this->finder();

        if (!$finder->hasResults()) {
            event(new Console('No spreadsheet file given', 'error'));
            return;
        }

        foreach ($finder as $file) {
            SeederMemoryHelper::memoryLog(__METHOD__ . '::' . __LINE__ . ' ' . 'file');
            event(new FileStart($file));
            event(new FileSeed($file));
            event(new FileFinish($file));
        }

        $this->tablesSeeded = $databaseWriter->tablesSeeded;

        $this->cleanup();
    }

    public function __set($name, $value) {
        $this->settings->$name = $value;
    }

    public function command() {
        return $this->command;
    }

    public function finder()
    {
        if ($this->settings->file instanceof Finder) return $this->settings->file;

        return new FileIterator();
    }

    public function cleanup()
    {
        $events = [
            Console::class,
            FileStart::class,
            FileSeed::class,
            FileFinish::class,
            SheetStart::class,
            SheetFinish::class,
            ChunkStart::class,
            ChunkFinish::class,
        ];

        foreach ($events as $event) Event::forget($event);
    }
}
