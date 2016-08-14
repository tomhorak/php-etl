<?php

namespace Marquine\Metis\Extractors;

use Marquine\Metis\Metis;
use Marquine\Metis\Traits\SetOptions;
use Marquine\Metis\Contracts\Extractor;

class Table implements Extractor
{
    use SetOptions;

    /**
     * The connection name.
     *
     * @var string
     */
    protected $connection = 'default';

    /**
     * The array of where clause.
     *
     * @var array
     */
    protected $where = [];

    /**
     * Extract data from the given source.
     *
     * @param  string $table
     * @param  mixed  $columns
     * @return array
     */
    public function extract($table, $columns = null)
    {
        if (is_string($columns)) {
            $columns = [$columns];
        }

        return Metis::connection($this->connection)->table($table)->select($columns, $this->where);
    }
}