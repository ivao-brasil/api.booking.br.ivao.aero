<?php

use App\Contracts\CSVFileServiceInterface;
use App\Contracts\Data\EventRepositoryInterface;
use App\Http\Controllers\EventDataExporterController;
use App\Models\Event;
use App\Models\Slot;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Contracts\Auth\Access\Gate;

class EventDataExporterControllerControllerTest extends TestCase
{
    /** @var EventRepositoryInterface&MockObject */
    private $eventRepository;

    /** @var CSVFileServiceInterface&MockObject */
    private $CSVFileService;

    /** @var Gate&MockObject */
    private $gate;

    private EventDataExporterController $EventDataExporterController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository = $this->createMock(EventRepositoryInterface::class);
        $this->CSVFileService = $this->createMock(CSVFileServiceInterface::class);
        $this->gate = $this->createMock(Gate::class);

        $this->EventDataExporterController = new EventDataExporterController(
            $this->eventRepository,
            $this->CSVFileService,
            $this->gate
        );
    }

    public function testCreateClass()
    {
        $this->assertInstanceOf(EventDataExporterController::class, $this->EventDataExporterController);
    }

    public function testCanExportEventData()
    {
        $testId = 1;
        $testCsvOutput = 'field1;field2\na;b\nc;d';
        $testEvent = $this->getTestEventWithSlots();

        $this->prepareAuthorizations();

        $this->eventRepository
            ->expects($this->once())
            ->method('getById')
            ->willReturn($testEvent);

        $this->CSVFileService
            ->expects($this->once())
            ->method('convertArrayToCSV')
            ->with(
                $this->anything(),
                $this->callback(fn ($subject) => is_array($subject))
            )
            ->willReturn($testCsvOutput);

        $result = $this->EventDataExporterController->__invoke($testId);

        $this->assertInstanceOf(StreamedResponse::class, $result);
    }

    /**
     * Get a slot collection to use in tests
     *
     * @return Event
     */
    private function getTestEventWithSlots(): Event
    {
        /** @var Event&MockObject */
        $event = $this->getModelMock(Event::class);
        /** @var Collection&MockObject */
        $collection = $this->createMock(Collection::class);

        $event->has_ended = true;

        $collection
            ->method('loadMissing')
            ->willReturnSelf();

        $collection
            ->method('map')
            ->willReturnSelf();

        $collection
            ->method('toArray')
            ->willReturn([
                new Slot([ 'origin' => 'a', 'destionation' => 'b' ]),
                new Slot([ 'origin' => 'c', 'destionation' => 'd' ]),
            ]);

        $event->slots = $collection;

        return $event;
    }

    private function prepareAuthorizations(): void
    {
        $this->gate
            ->expects($this->once())
            ->method('authorize')
            ->with(
                $this->equalTo('export'),
                $this->isInstanceOf(Event::class)
            );
    }
}
