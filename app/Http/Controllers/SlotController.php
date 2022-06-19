<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Slot;
use App\Models\User;
use App\Services\PaginationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ParseCsv\Csv;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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
        /** @var \App\Models\Slot|null */
        $slot = Slot::find($slotId);
        /** @var \App\Models\User|null */
        $user = Auth::user();

        if (!$slot) {
            return abort(404, 'book.notFound');
        }

        $this->authorize('bookUpdate', [$slot, $action]);

        if ($action === 'book') {
            if ($slot->private) {
                $this->validate($request, [
                    'flightNumber' => 'required|string|max:7',
                    'origin' => 'required|string|max:4',
                    'destination' => 'required|string|max:4',
                    'aircraft' => 'required|string|max:4',
                ]);

                //If the airport is not found, this function will eventually abort()
                AirportController::getAirportByICAO($request->input('origin'));
                AirportController::getAirportByICAO($request->input('destination'));


                //TODO: This is another instance of the ->count() need (just like the SlotPolicy).
                if($slot->event->slots->where('flightNumber', $request->input('flightNumber'))->count() > 0){
                    abort(403, "book.duplicateNumber");
                }

                $slot->fill($request->all());

            }

            /** @var \App\Models\Event */
            $slotEvent = $slot->event;

            $slot->bookingStatus = $slotEvent->can_auto_book ? 'booked' : 'prebooked';

            //Cycle through the user slots and checks for overlapping slots.
            foreach($user->slotsBooked->where('eventId', $slot->event->id) as $bookedSlot) {
                if(SlotController::checkOverlappingSlots($slot, $bookedSlot)) {
                    return abort(403, 'book.alreadyBusy');
                }
            }

            $slot->bookingTime = Carbon::now();

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
                          ->where('private', $value);

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
            ->where('private', 0)
            ->count();

        $landingCount = Slot::where('eventId', $eventId)
            ->where('type', 'landing')
            ->where('private', 0)
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

    public function listOverlappingSlots($eventId)
    {
        $this->authorize('listOverlapping', Slot::class);

        $event = Event::where('id', $eventId)->first();

        $pilots = $event->slots->where('bookingStatus', '!=', 'free')
                              ->groupBy('pilotId');

        $pilots = $pilots->mapWithKeys( function($slotList, $pilotId) {
            $slotList = $slotList->filter( function($slotOne) use ($slotList) {
                foreach($slotList as $slotTwo) {
                    if($slotOne->id == $slotTwo->id) continue;
                    return SlotController::checkOverlappingSlots($slotOne, $slotTwo);
                }
            });

            return [User::where('id', $pilotId)->first()->vid => $slotList];
        });

        $pilots = $pilots->filter( function ($slotList, $pilotId) {
            return count($slotList) > 0;
        });

        return $pilots;
    }

    /*
     *  Checks if two slots are overlapping
     */
    public static function checkOverlappingSlots($slotOne, $slotTwo)
    {
        //If the slots belong to different events, just return false
        if($slotOne->eventId != $slotTwo->eventId) {
            return false;
        }

        //Get the timestamps (departure and arrival) from both slots
        $slotOneTimestamp = SlotController::getSlotTimestamps($slotOne);
        $slotTwoTimestamp  = SlotController::getSlotTimestamps($slotTwo);

        //SlotOne ENDS BEFORE SlotTwo starts
        $case1 = $slotOneTimestamp['arrival'] < $slotTwoTimestamp['departure'];

        //SlotTwo ENDS BEFORE SlotOne starts
        $case2 = $slotTwoTimestamp['arrival'] < $slotOneTimestamp['departure'];

        return $case1 == false && $case2 == false;
    }

    //Gets Departure and Arrival timestamps for a given slot
    public static function getSlotTimestamps($slot)
    {

        //Although I understand this is not the best way of doing it, I believe it is more readable.
        if(Cache::has($slot->id . '_timestamps')) {
            return Cache::get($slot->id . '_timestamps');
        }

        //First we determine which day the slot is in.
        $dateStart = new Carbon($slot->event->dateStart);
        $dateEnd = new Carbon($slot->event->dateEnd);

        $slotTime = $dateStart;

        if($dateStart->day != $dateEnd->day){
            if($slot->slotTime < 1200) {
                $slotTime->addDay();
            }
        }

        //After we know the day, we set the ours for the slot
        $slotTime->hours(substr($slot->slotTime, 0, 2));
        $slotTime->minutes(substr($slot->slotTime, 2, 2));

        //We will set start and end times for the flight
        if($slot->type === 'takeoff') {
            $departure  = $slotTime->timestamp;
            $arrival    = $slotTime->addSeconds($slot->getFlightTimeAttribute())->timestamp;
        } else if($slot->type === 'landing') {
            $arrival   = $slotTime->timestamp;
            $departure = $slotTime->subSeconds($slot->getFlightTimeAttribute())->timestamp;
        }

        $timestamps = [
            'departure' => round($departure),
            'arrival'   => round($arrival)
        ];

        //We will store this in cache indefinitely
        Cache::put($slot->id . '_timestamps', $timestamps);

        return $timestamps;
    }

    public static function getFlightTime($slot){
        $distance = AirportController::getCircleDistanceBetweenAirports($slot->origin, $slot->destination);
        return AircraftController::getFlightTimeFromICAO($slot->aircraft, $distance);
    }

}
