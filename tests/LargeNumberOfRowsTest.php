<?php

namespace bfinlay\SpreadsheetSeeder\Tests;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeederServiceProvider;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\FakeNamesCsvSeeder;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\FakeNamesXlsxSeeder;
use Orchestra\Testbench\TestCase;

class LargeNumberOfRowsTest extends TestCase
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
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
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
            'Gender',
            'Title',
            'GivenName',
            'MiddleInitial',
            'Surname',
            'StreetAddress',
            'City',
            'State',
            'ZipCode',
            'created_at',
            'updated_at'
        ], \Schema::getColumnListing('fake_names'));
    }

    /**
     * @depends it_runs_the_migrations
     *
     * Seed excel spreadsheet and verify that worksheet names match table names
     *
     * Seed classicmodels.xlsx and verify some data in each table
     * Tables are created by the migrations.  In order to test the seed process the test has to verify that data was
     * loaded from a sheet in the spreadsheet to the corresponding table.
     *
     */
    public function test_15k_rows()
    {
        $this->seed(FakeNamesCsvSeeder::class);

        $fake = \DB::table('fake_names')->where('id', '=', 15000)->first();
        $this->assertEquals('Culver', $fake->Surname);
        $this->assertEquals('Alicia', $fake->GivenName);
    }

    /**
     * @depends it_runs_the_migrations
     *
     * Seed excel spreadsheet and verify that worksheet names match table names
     *
     * Seed classicmodels.xlsx and verify some data in each table
     * Tables are created by the migrations.  In order to test the seed process the test has to verify that data was
     * loaded from a sheet in the spreadsheet to the corresponding table.
     *
     */
    public function test_15k_xlsx_rows()
    {
        $this->seed(FakeNamesXlsxSeeder::class);

        $fake = \DB::table('fake_names')->where('id', '=', 15000)->first();
        $this->assertEquals('Culver', $fake->Surname);
        $this->assertEquals('Alicia', $fake->GivenName);
    }
}
