<?php

namespace App\Contracts;

interface CSVFileServiceInterface
{
    /**
     * Converts an array to a .csv compatible string
     *
     * @param array $columns
     * @param array $data
     * @return string
     */
    public function convertArrayToCSV(array $columns, array $data): string;
}
