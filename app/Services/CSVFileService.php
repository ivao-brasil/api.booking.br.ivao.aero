<?php

namespace App\Services;

use App\Contracts\CSVFileServiceInterface;
use ParseCsv\Csv;

class CSVFileService implements CSVFileServiceInterface
{
    private Csv $csv;

    public function __construct(Csv $csv)
    {
        $this->csv = $csv;
    }

    /**
     * Converts an array to a .csv compatible string
     *
     * @param array $columns
     * @param array $data
     * @return string
     */
    public function convertArrayToCSV(array $columns, array $data): string
    {
        return $this->csv->unparse($data, $columns);
    }
}
