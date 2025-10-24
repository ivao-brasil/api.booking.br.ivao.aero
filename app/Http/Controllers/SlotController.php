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

    private static $slotDataValidationRules = [
        'flightNumber' => 'nullable|string|max:10|regex:/^[A-Z0-9]+$/',
        'origin' => 'nullable|string|regex:/^[A-Z]{4}$/|isAirportExistent',
        'destination' => 'nullable|string|regex:/^[A-Z]{4}$/|isAirportExistent',
        'gate' => 'nullable|string|alpha_num|max:10',
        'aircraft' => 'nullable|string|regex:/^[A-Z0-9]{4}$/',
        'eobtOrigin' => 'nullable|date_format:Y-m-d H:i',
        'etaDestination' => 'nullable|date_format:Y-m-d H:i',
    ];

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

        $this->validateFullSlot($request);

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
            abort(404, 'book.notFound');
        }

        $this->authorize('bookUpdate', [$slot, $action]);

        if ($action === 'book') {
            $validationRules = ['eobtOrigin', 'etaDestination', 'gate'];
            if(!$slot->isFixedFlightNumber) {
                $validationRules[] = 'flightNumber';
            }
            else {
                $request->merge(['flightNumber' => $slot->flightNumber]);
            }

            if(!$slot->isFixedOrigin) {
                $validationRules[] = 'origin';
            }
            else {
                $request->merge(['origin' => $slot->origin]);
            }

            if(!$slot->isFixedDestination) {
                $validationRules[] = 'destination';
            }
            else {
                $request->merge(['destination' => $slot->destination]);
            }

            if(!$slot->isFixedAircraft) {
                $validationRules[] = 'aircraft';
            }
            else {
                $request->merge(['aircraft' => $slot->aircraft]);
            }

            if($slot->isFixedeobtOrigin) {
                $request->merge(['eobtOrigin' => $slot->eobtOrigin]);
            }

            if($slot->isFixedEtaDestination) {
                $request->merge(['etaDestination' => $slot->etaDestination]);
            }

            $this->validate($request, array_intersect_key(
                self::$slotDataValidationRules,
                $validationRules
            ));

            if($slot->event->slots->where('flightNumber', $request->input('flightNumber'))
                    ->where('isFixedFlightNumber', false)
                    ->count() > 0) {
                abort(422, "book.duplicateNumber");
            }

            $slot->fill($request->all());

            /** @var \App\Models\Event */
            $slotEvent = $slot->event;

            $slot->bookingStatus = $slotEvent->can_auto_book ? 'booked' : 'prebooked';

            //Cycle through the user slots and checks for overlapping slots.
            foreach($user->slotsBooked->where('eventId', $slot->event->id) as $bookedSlot) {
                if(SlotController::checkOverlappingSlots($slot, $bookedSlot)) {
                    abort(422, 'book.alreadyBusy');
                }
            }

            $slot->bookingTime = Carbon::now();

            $user->slotsBooked()->save($slot);
        } else if ($action === "cancel") {
            if(!$slot->isFixedFlightNumber) {
                $slot->flightNumber = null;
            }

            if(!$slot->isFixedOrigin) {
                $slot->origin = null;
            }

            if(!$slot->isFixedDestination) {
                $slot->destination = null;
            }

            if(!$slot->isFixedAircraft) {
                $slot->aircraft = null;
            }

            if(!$slot->isFixedeobtOrigin) {
                $slot->eobtOrigin = null;
            }

            if(!$slot->isFixedEtaDestination) {
                $slot->etaDestination = null;
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

        $this->validateFullSlot($request);
        $slot->fill($request->all());
        $slot->save();
    }

    public function list(string $eventId, Request $request)
    {
        $perPage = (int)$request->query('perPage', 25);

        $slots = Slot::with('owner')->where('eventId', $eventId);

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

            //This selects only slots from a given ICAO code (ABC, AZU, etc)
            if ($param == "airline") {
                $slots = $slots
                          ->where('flightNumber', "LIKE", $request->input("airline") . "%");

                continue;
            }

            if($param == "type") {
                if($value == "takeoff") {
                    $slots = $slots->where('isFixedOrigin', 1)
                                   ->where('isFixedDestination', 0);
                } else if($value == "landing") {
                    $slots = $slots->where('isFixedOrigin', 0)
                                   ->where('isFixedDestination', 1);
                } else if($value == "takeoff_landing") {
                    $slots = $slots->where('isFixedOrigin', 1)
                                   ->where('isFixedDestination', 1);
                }
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
            $slots->paginate(min($perPage, 25))
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
            if (isset($data['eobtOrigin']) && $data['eobtOrigin'] == '') {
                $data['eobtOrigin'] = null;
            }
            if (isset($data['etaDestination']) && $data['etaDestination'] == '') {
                $data['etaDestination'] = null;
            }
            return $data;
        })->toArray();

        Slot::insert($slots);
    }

    public function getEventSlotCountByType(string $eventId) {
        $takeoffCount = Slot::where('eventId', $eventId)
            ->where('isFixedOrigin', 1)
            ->where('isFixedDestination', 0)
            ->count();

        $landingCount = Slot::where('eventId', $eventId)
            ->where('isFixedOrigin', 0)
            ->where('isFixedDestination', 1)
            ->count();

        $takeoffAndLanding = Slot::where('eventId', $eventId)
            ->where('isFixedOrigin', 1)
            ->where('isFixedDestination', 1)
            ->count();

        return response()->json([
            'departure' => $takeoffCount,
            'landing'   => $landingCount,
            'departureLanding'   => $takeoffAndLanding
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

        //SlotTwo ENDS BEFORE SlotOne starts
        $case2 = $slotTwo->eobtOrigin < $slotOne->etaDestination;

        return $case1 == false && $case2 == false;
    }

    public function isAirportExistent($attribute, $value, $parameters, $validator) {
        return AirportController::getAirportByICAO($value); // Custom condition
    }

    public function validateFullSlot(Request $request): void
    {
        $validationRules = ['gate', 'eobtOrigin', 'etaDestination'];

        if ($request->input('origin')) {
            $validationRules[] = 'origin';
        } else {
            $request->merge(['isFixedOrigin' => 1]);
        }

        if ($request->input('destination')) {
            $validationRules[] = 'destination';
        } else {
            $request->merge(['isFixedDestination' => 1]);
        }

        if ($request->input('aircraft')) {
            $validationRules[] = 'aircraft';
        } else {
            $request->merge(['isFixedAircraft' => 1]);
        }

        if ($request->input('flightNumber')) {
            $validationRules[] = 'flightNumber';
        } else {
            $request->merge(['isFixedFlightNumber' => 1]);
        }

        if(!$request->input('isFixedOrigin') && !$request->input('isFixedDestination')) {
            abort(422, 'slot.invalidSlot');
        }

        $this->validate($request, array_intersect_key(
            self::$slotDataValidationRules,
            $validationRules
        ));
    }

}
