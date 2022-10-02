<?php

use App\Contracts\CSVFileServiceInterface;
use App\Contracts\Data\EventRepositoryInterface;
use App\Http\Controllers\EventDataExporter;
use App\Models\Event;
use App\Services\CSVFileService;
use ParseCsv\Csv;
use PHPUnit\Framework\MockObject\MockObject;

class CSVFileServiceTest extends TestCase
{
    /** @var Csv&MockObject */
    private $csvParser;

    private CSVFileServiceInterface $CSVFileService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->csvParser = $this->createMock(Csv::class);
        $this->CSVFileService = new CSVFileService($this->csvParser);
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf(CSVFileService::class, $this->CSVFileService);
    }

    public function testCanExportCSVData()
    {
        $testFields = [
            "field1",
            "field2"
        ];

        $testData = [
            ["a", "b"],
            ["c", "d"]
        ];

        $expectedCsvOutput = 'field1;field2\na;b\nc;d';

        $this->csvParser
            ->expects($this->once())
            ->method('unparse')
            ->with(
                $this->identicalTo($testData),
                $this->identicalTo($testFields)
            )
            ->willReturn($expectedCsvOutput);

        $result = $this->CSVFileService->convertArrayToCSV($testFields, $testData);

        $this->assertEquals($expectedCsvOutput, $result);
    }
}
