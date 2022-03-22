<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Slot;
use App\Services\PaginationService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ParseCsv\Csv;
use Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Support\Facades\Log;

class SlotController extends Controller
{

    private $paginationService;


    public function __construct(PaginationService $paginationService)
    {
        $this->paginationService = $paginationService;
    }

    public function create(Request $request, $eventId)
    {
        $this->authorize('create', Slot::class);

        $event = Event::find($eventId);

        if (!$event) {
            abort(404, 'event.notFound');
        }

        $this->validate($request, ['private' => 'required|boolean']);

        if ($request->input('private')) {
            $this->validate($request, [
                'type' => 'required|string',
                'flightNumber' => 'string|max:7',
                'origin' => 'string|max:4',
                'destination' => 'string|max:4',
                'slotTime' => 'required|string|max:4',
                'gate' => 'required|string|max:10',
                'aircraft' => 'string|max:4',
            ]);
        } else {
            $this->validate($request, [
                'type' => 'required|string',
                'flightNumber' => 'required|string|max:7',
                'origin' => 'required|string|max:4',
                'destination' => 'required|string|max:4',
                'slotTime' => 'required|required|string|max:4',
                'gate' => 'required|string|max:10',
                'aircraft' => 'required|string|max:4',
            ]);
        }

        $slot = new Slot();
        $slot->fill($request->all());
        $event->slots()->save($slot);
    }

    public function delete(string $slotId)
    {
        $this->authorize('delete', Slot::class);
        Slot::where('id', $slotId)->delete();
    }

    public function book(Request $request, string $slotId, string $action)
    {
        $slot = Slot::find($slotId);
        /** @var \App\Models\User|null */
        $user = Auth::user();

        if (!$slot) {
            abort(404, 'book.notFound');
        }

        $this->authorize('bookUpdate', [ $slot, $action]);

        if ($action === 'book') {
            if ($slot->private) {
                $this->validate($request, [
                    'flightNumber' => 'required|string|max:7',
                    'origin' => 'required|string|max:4',
                    'destination' => 'required|string|max:4',
                    'aircraft' => 'required|string|max:4',
                ]);

                //TODO: This is another instance of the ->count() need (just like the SlotPolicy).
                if($slot->event->slots->where('flightNumber', $request->input('flightNumber'))->count() > 0){
                    abort(403, "book.duplicateNumber");
                }

                $slot->fill($request->all());
            }

            /** @var \App\Models\Event */
            $slotEvent = $slot->event;
            /** @var \Carbon\Carbon */
            $eventStartDate = $slotEvent->dateStart;
            $now = Carbon::now();

            if ($now->greaterThan($eventStartDate)) {
                abort(403, "book.hasStarted");
            }

            $hoursBeforeStart = $now->diffInHours($eventStartDate, false);
            $ignoreConfirmationHours = config('app.slot.ignore_slot_confirmation_hours');

            if ($now->lessThanOrEqualTo($eventStartDate) && $ignoreConfirmationHours >= $hoursBeforeStart) {
                $slot->bookingStatus = 'booked';
            } else {
                $slot->bookingStatus = 'prebooked';
            }

            $slot->bookingTime = (new DateTime())->format("Y-m-d H:i:s");

            $user->slotsBooked()->save($slot);
        } else if ($action === "cancel") {

            if ($slot->private) {
                if($slot->type === 'takeoff') {
                    $slot->destination = null;
                } else if ($slot->type === 'landing') {
                    $slot->origin = null;
                }

                $slot->flightNumber = null;
                $slot->aircraft = null;
            }

            $slot->bookingTime = null;
            $slot->pilotId = null;
            $slot->bookingStatus = 'free';
            $slot->save();
        } else if ($action === "confirm") {
            if ($slot->bookingStatus !== "prebooked") {
                abort(400, "The slot is not prebooked");
            }

            $slot->bookingStatus = "booked";
            $slot->save();
        }
    }

    public function update(Request $request, $slotId)
    {
        $this->authorize('create', Slot::class);

        $slot = Slot::find($slotId);

        if (!$slot) {
            abort(404, 'book.notFound');
        }

        $this->validate($request, ['private' => 'required|boolean']);

        if ($request->input('private')) {
            $this->validate($request, [
                'type' => 'required|string',
                'flightNumber' => 'string|max:7',
                'origin' => 'string|max:4',
                'destination' => 'string|max:4',
                'slotTime' => 'required|string|max:4',
                'gate' => 'required|string|max:10',
                'aircraft' => 'string|max:4',
            ]);

            $slot->flightNumber = null;
            $slot->origin = null;
            $slot->destination = null;
            $slot->aircraft = null;
        } else {
            $this->validate($request, [
                'type' => 'required|string',
                'flightNumber' => 'required|string|max:7',
                'origin' => 'required|string|max:4',
                'destination' => 'required|string|max:4',
                'slotTime' => 'required|required|string|max:4',
                'gate' => 'required|string|max:10',
                'aircraft' => 'required|string|max:4',
            ]);
        }

        $slot->fill($request->all());

        $slot->save();
    }

    public function list(string $eventId, Request $request)
    {
        $perPage = (int)$request->query('perPage', 5,);

        $slots = Slot::with('owner')->where('eventId', $eventId)->orderBy('slotTime');

        $queryParams = (array)$request->query();

        //Cycle through the parameters
        foreach ($queryParams as $param => $value) {

            //This selects only the available slots
            if ($param == "available") {
                $slots = $slots
                    ->doesntHave("owner")
                    ->where("bookingStatus", "free");

                continue;
            }

            //This validates only private slots
            if ($param == "private") {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                
                $slots = $slots
                          ->where('private', $param);
                
                continue;
            }

            //This selects only slots from a given ICAO code (ABC, AZU, etc)
            if ($param == "airline") {
                $slots = $slots
                          ->where('flightNumber', "LIKE", $request->input("airline") . "%");

                continue;
            }

            //This filters the rest of the parameters
            if (!in_array($param, Slot::$allowedQueryParams)) {
                continue;
            }

            //If nothing else happens, queries it.
            $slots = $slots->where($param, 'LIKE', "%" . $value . "%");
        }

        return $this->paginationService->transform(
            $slots->paginate($perPage > 25 ? 25 : $perPage)
        );
    }

    public function getMySlots(string $eventId, Request $request)
    {
        /** @var \App\Models\User|null */
        $user = Auth::user();

        $perPage = (int)$request->query('perPage', 5,);
        $queryFlightNumber = (string)$request->query("flightNumber");

        $mySlots = Slot::with('event')
            ->where('eventId', $eventId)
            ->where('pilotId', $user->id);

        if ($queryFlightNumber) {
            $mySlots->where("flightNumber", $queryFlightNumber);
        }

        return $this->paginationService->transform(
            $mySlots->paginate($perPage > 25 ? 25 : $perPage)
        );
    }

    public function getTemplate()
    {
        return Storage::download('template.csv');
    }

    public function createMany(string $eventId, Request $request)
    {
        $file = $request->file('file');

        if ($file->getSize() >= 1024 * 1024) {
            throw new UnprocessableEntityHttpException();
        }

        $content = $file->getContent();

        $csv = new Csv();
        $csv->auto($content);

        $slots = collect($csv->data)->map(function ($data) use ($eventId) {
            $data['eventId'] = $eventId;
            return $data;
        })->toArray();

        Slot::insert($slots);
    }

    public function getEventSlotCountByType(string $eventId) {
        $takeoffCount = Slot::where('eventId', $eventId)
            ->where('type', 'takeoff')
            ->count();

        $landingCount = Slot::where('eventId', $eventId)
            ->where('type', 'landing')
            ->count();

        $privateCount = Slot::where('eventId', $eventId)
            ->where('private', 1)
            ->count();

        return response()->json([
            'departure' => $takeoffCount,
            'landing'   => $landingCount,
            'private'   => $privateCount
        ]);
    }
}
