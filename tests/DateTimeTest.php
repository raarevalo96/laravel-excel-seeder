<?php

namespace bfinlay\SpreadsheetSeeder\Tests;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeederServiceProvider;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\ClassicModelsSeeder;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\DateTimeTest\DateTimeSeeder;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Carbon;

class DateTimeTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        // and other test setup steps you need to perform
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [SpreadsheetSeederServiceProvider::class];
    }

    /** @test */
    public function it_runs_the_migrations()
    {
        $this->assertEquals([
            'id',
            'order_date',
            'required_date',
            'shipped_date',
            'status',
            'comments',
            'customer_id',
            'created_at',
            'updated_at'
        ], \Schema::getColumnListing('orders'));
    }

    /**
     * Seed excel spreadsheet and verify that order dates are properly populated
     *
     * Seed classicmodels.xlsx and verify dates in order table
     *
     * @depends it_runs_the_migrations
     */
    public function test_order_dates()
    {
        $this->seed(ClassicModelsSeeder::class);

        $order = \DB::table('orders')->where('id', 10367)->first();
        $this->assertEquals(205, $order->customer_id);
        $this->assertEquals(326, \DB::table('orders')->count());
        $this->assertEquals((new Carbon('January 12 2005')), new Carbon($order->order_date));
        $this->assertEquals((new Carbon('January 21 2005')), new Carbon($order->required_date));
        $this->assertEquals((new Carbon('January 16 2005')), new Carbon($order->shipped_date));
    }

    /**
     * @depends it_runs_the_migrations
     */
    public function test_date_formats()
    {
        $startSeedDate = date('Y-m-d H:i:s.u');
        $this->seed(DateTimeSeeder::class);

        $rows = \DB::table('date_time_test')->get();
        $fetchRowsDate = date('Y-m-d H:i:s.u');

        $row = $rows[0];
        $this->assertEquals((new Carbon('October 15 2020 23:37')), new Carbon($row->excel_format));
        $this->assertEquals((new Carbon('October 16 2020 04:37:09')), new Carbon($row->unix_format));
        $this->assertEquals((new Carbon('October 04 2020 05:31:02.44')), new Carbon($row->string_format_1));
        $this->assertEquals((new Carbon('October 15 2020')), new Carbon($row->string_format_2));
        $this->assertGreaterThanOrEqual(new Carbon($startSeedDate), new Carbon($row->created_at));
        $this->assertLessThanOrEqual(new Carbon($fetchRowsDate), new Carbon($row->created_at));
        $this->assertGreaterThanOrEqual(new Carbon($startSeedDate), new Carbon($row->updated_at));
        $this->assertLessThanOrEqual(new Carbon($fetchRowsDate), new Carbon($row->updated_at));

        $columns = ["excel_format", "unix_format", "string_format_1", "string_format_2", "created_at", "updated_at"];

        // empty cells
        $row = $rows[1];
        foreach($columns as $column) {
            $this->assertGreaterThanOrEqual(new Carbon($startSeedDate), new Carbon($row->{$column}));
            $this->assertLessThanOrEqual(new Carbon($fetchRowsDate), new Carbon($row->{$column}));
        }

        // explicit 'null' cells
        // TODO determine if these should be null instead of default dates
        $row = $rows[2];
        foreach($columns as $column) {
            $this->assertGreaterThanOrEqual(new Carbon($startSeedDate), new Carbon($row->{$column}));
            $this->assertLessThanOrEqual(new Carbon($fetchRowsDate), new Carbon($row->{$column}));
        }
    }
}
