<?php

namespace bfinlay\SpreadsheetSeeder\Tests\Workaround\RefreshDatabase;

use Illuminate\Database\MySqlConnection as BaseMySqlConnection;

class RefreshDatabaseMySqlConnection extends BaseMySqlConnection
{
    /**
     * Perform a rollback within the database.
     *
     * @param  int  $toLevel
     * @return void
     *
     * @throws \Throwable
     */
    protected function performRollBack($toLevel)
    {
        if ($toLevel == 0) {
            $pdo = $this->getPdo();

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } elseif ($this->queryGrammar->supportsSavepoints()) {
            $this->getPdo()->exec(
                $this->queryGrammar->compileSavepointRollBack('trans'.($toLevel + 1))
            );
        }
    }
}