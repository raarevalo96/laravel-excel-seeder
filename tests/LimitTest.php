<?php

namespace bfinlay\SpreadsheetSeeder\Tests;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeederServiceProvider;
use bfinlay\SpreadsheetSeeder\Tests\LargeNumberOfRowsTest;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\LimitTest\LimitSeeder;
use Orchestra\Testbench\TestCase;

class LimitTest extends TestCase
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
     * @Depends LargeNumberOfRowsTest::test_15k_xlsx_rows
     */
    public function test_limit()
    {
        // limit seeder sets $limit setting to 5000 to seed only the first 5000 rows
        $this->seed(LimitSeeder::class);

        // check that exactly 5000 rows are seeded and no more
        $this->assertEquals(5000, \DB::table('fake_names')->count());

        // check row 5000 is seeded
        $fake = \DB::table('fake_names')->where('id', '=', 5000)->first();
        $this->assertEquals('Venuti', $fake->Surname);
        $this->assertEquals('Samuel', $fake->GivenName);
    }
}
