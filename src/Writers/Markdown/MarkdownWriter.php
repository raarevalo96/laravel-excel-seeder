<?php


namespace bfinlay\SpreadsheetSeeder\Writers\Markdown;


use bfinlay\SpreadsheetSeeder\Readers\Events\ChunkFinish;
use bfinlay\SpreadsheetSeeder\Readers\Events\ChunkStart;
use bfinlay\SpreadsheetSeeder\Readers\Events\FileStart;
use bfinlay\SpreadsheetSeeder\Readers\Events\SheetFinish;
use bfinlay\SpreadsheetSeeder\Readers\Events\SheetStart;
use Illuminate\Support\Facades\Event;

class MarkdownWriter
{
    /**
     * @var TextOutputWriter
     */
    protected $textOutputWriter;

    public function run()
    {
        Event::listen(FileStart::class, [$this, 'handleFileStart']);
        Event::listen(SheetStart::class, [$this, 'handleSheetStart']);
        Event::listen(ChunkFinish::class, [$this, 'handleChunkFinish']);
        Event::listen(SheetFinish::class, [$this, 'handleSheetFinish']);
    }

    /**
     * @param $fileStart FileStart
     */
    public function handleFileStart($fileStart)
    {
        $this->textOutputWriter = new TextOutputWriter($fileStart->file);
    }

    /**
     * @param $sheetStart SheetStart
     */
    public function handleSheetStart($sheetStart)
    {
        $this->textOutputWriter->openSheet($sheetStart->tableName, $sheetStart->header);
    }

    /**
     * @param $chunkFinish ChunkFinish
     */
    public function handleChunkFinish($chunkFinish)
    {
        $this->textOutputWriter->saveChunk($chunkFinish->rows->rawRows);
    }

    /**
     * @param $sheetFinish SheetFinish
     */
    public function handleSheetFinish($sheetFinish)
    {
        $this->textOutputWriter->closeSheet();
    }
}