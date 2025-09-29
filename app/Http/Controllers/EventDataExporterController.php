<?php

namespace App\Http\Controllers;

use App\Contracts\CSVFileServiceInterface;
use App\Contracts\Data\EventRepositoryInterface;
use App\Models\Event;
use App\Models\Slot;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Collection;

class EventDataExporterController extends Controller
{
    /** @var string[] */
    private const CSV_COLUMNS = [
        'id',
        'flightNumber',
        'origin',
        'destination',
        'slotTime',
        'gate',
        'aircraft',
        'owner'
    ];

    private EventRepositoryInterface $eventRepository;
    private CSVFileServiceInterface $CSVFileService;
    private Gate $gate;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        CSVFileServiceInterface $CSVFileService,
        Gate $gate
    ) {
        $this->eventRepository = $eventRepository;
        $this->CSVFileService = $CSVFileService;
        $this->gate = $gate;
    }

    /**
     * Handle the incoming request.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function __invoke(int $id)
    {
        $event = $this->eventRepository->getById($id);

        $this->gate->authorize('export', $event);

        $eventSlots = $this->getEventSlots($event);
        $slotsArray = $this->convertSlotsCollectionToArray($eventSlots);
        $csvString = $this->CSVFileService->convertArrayToCSV(self::CSV_COLUMNS, $slotsArray);

        $fileName = "event_$id.csv";

        return response()->stream(
            fn() => $this->writeCSVDataStream($csvString),
            200,
            $this->getFileResponseHeaders($fileName)
        );
    }

    private function getEventSlots(Event $event): Collection
    {
        $slotCollection = $event->slots->loadMissing('owner');
        return $slotCollection;
    }

    private function convertSlotsCollectionToArray(Collection $slots): array
    {
        $result = $slots->map(function (Slot $slot) {
            $ownerVid = $slot->owner->vid ?? null;
            $result = $slot->toArray();
            $result['owner'] = $ownerVid;

            return $result;
        });

        return $result->toArray();
    }

    private function getFileResponseHeaders($filename): array
    {
        return [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];
    }

    private function writeCSVDataStream(string $csvData): void
    {
        $fileStream = fopen('php://output', 'w');
        fwrite($fileStream, $csvData);
        fclose($fileStream);
    }
}
