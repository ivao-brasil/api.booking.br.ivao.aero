<?php

namespace App\Http\Controllers;

use App\Contracts\CSVFileServiceInterface;
use App\Contracts\Data\EventRepositoryInterface;

class EventDataExporter extends Controller
{
    private EventRepositoryInterface $eventRepository;
    private CSVFileServiceInterface $CSVFileService;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        CSVFileServiceInterface $CSVFileService
    ) {
        $this->eventRepository = $eventRepository;
        $this->CSVFileService = $CSVFileService;
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
        $eventSlots = $event->slots->toArray();
        $csvString = $this->CSVFileService->convertArrayToCSV(['a', 'b'], $eventSlots);

        $callback = function () use ($csvString) {
            $fileStream = fopen('php://output', 'w');
            fwrite($fileStream, $csvString);
            fclose($fileStream);
        };

        $fileName = "event_$id.csv";

        return response()->stream(
            $callback,
            200,
            $this->getFileResponseHeaders($fileName)
        );
    }

    private function getFileResponseHeaders($filename)
    {
        return [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename='.$filename,
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];
    }
}
