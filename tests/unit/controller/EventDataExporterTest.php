<?php

use App\Contracts\CSVFileServiceInterface;
use App\Contracts\Data\EventRepositoryInterface;
use App\Http\Controllers\EventDataExporter;
use App\Models\Event;
use App\Models\Slot;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventDataExporterTest extends TestCase
{
    /** @var EventRepositoryInterface&MockObject */
    private $eventRepository;

    /** @var CSVFileServiceInterface&MockObject */
    private $CSVFileService;

    /** @var Event&MockObject */
    private $event;

    private EventDataExporter $eventDataExporter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository = $this->createMock(EventRepositoryInterface::class);
        $this->CSVFileService = $this->createMock(CSVFileServiceInterface::class);
        $this->event = $this->getModelMock(Event::class);

        $this->eventDataExporter = new EventDataExporter(
            $this->eventRepository,
            $this->CSVFileService
        );
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf(EventDataExporter::class, $this->eventDataExporter);
    }

    public function testCanExportEventData()
    {
        $testId = 1;
        $testSlots = $this->getTestSlotCollection();
        $testCsvOutput = 'field1;field2\na;b\nc;d';

        $this->event->slots = $testSlots;

        $this->eventRepository
            ->expects($this->once())
            ->method('getById')
            ->willReturn($this->event);

        $this->CSVFileService
            ->expects($this->once())
            ->method('convertArrayToCSV')
            ->with(
                $this->anything(),
                $this->equalTo($testSlots->toArray())
            )
            ->willReturn($testCsvOutput);

        $result = $this->eventDataExporter->__invoke($testId);

        $this->assertInstanceOf(StreamedResponse::class, $result);
    }

    /**
     * Get a slot collection to use in tests
     *
     * @return \Illuminate\Database\Eloquent\Collection|\App\Models\Slot[]
     */
    private function getTestSlotCollection()
    {
        $collection = new Slot();
        $collection->newCollection([
            new Slot([ 'origin' => 'a', 'destionation' => 'b' ]),
            new Slot([ 'origin' => 'c', 'destionation' => 'd' ]),
        ]);

        return $collection;
    }
}
