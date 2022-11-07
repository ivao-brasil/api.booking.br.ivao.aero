<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventAirport;
use App\Services\PaginationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    private $paginationService;

    function __construct(PaginationService $paginationService)
    {
        $this->paginationService = $paginationService;
    }

    public function create(Request $request)
    {
        //$this->authorize('create', Event::class);
        
        if(!Auth::user()->admin)
            return abort(403, 'admin.notAdmin');


        $this->validate($request, [
            'atcBooking' => 'required|url',
            'atcBriefing' => 'url',
            'banner' => 'required|url',
            'dateStart' => 'required|numeric',
            'dateEnd' => 'required|numeric|gt:dateStart',
            'description' => 'required|string',
            'eventName' => 'required|string|max:255',
            'pilotBriefing' => 'url',
            'privateSlots' => 'boolean',
            'publicAccess' => 'boolean',
            'airports' => 'required|string',
            'type' => 'required|string',
        ]);

        $user = Auth::user();

        $dateStart = new Carbon();
        $dateEnd = new Carbon();

        $dateStart->setTimestamp($request->input('dateStart'));
        $dateEnd->setTimestamp($request->input('dateEnd'));

        if($dateStart->diffInHours($dateEnd) > 10) {
            return abort(403, 'event.tooLong');
        }

        $event = new Event();

        $event->fill([
            'division' => $user->division,
            'dateStart' => $dateStart->toDateTimeString(),
            'dateEnd' => $dateEnd->toDateTimeString(),
            'eventName' => $request->input('eventName'),
            'privateSlots' => $request->input('privateSlots'),
            'status' => 'created',
            'createdBy' => $user->id,
            'pilotBriefing' => $request->input('pilotBriefing'),
            'atcBriefing' => $request->input('atcBriefing'),
            'description' => $request->input('description'),
            'atcBooking' => $request->input('atcBooking'),
            'banner' => $request->input('banner'),
            'type' => $request->input('type'),
        ]);

        $event->save();
        $event->refresh();

        self::setAirports($event->id, $request->input('airports'));

        return response(null, 201);
    }

    /**
     * MINIMUM VIABLE PRODUCT
     *
     * TODO: IMPROVE THIS THING TO AVOID UNECESSARY CALLS TO THE DATABASE
     */
    private static function setAirports($eventId, $airportList)
    {
        EventAirport::where('eventId', $eventId)->delete();

        foreach (explode(',', $airportList) as $icao) {
            $airport = new EventAirport;

            $airport->eventId = $eventId;
            $airport->icao = $icao;

            $airport->save();
        }
    }

    public function get(Request $request)
    {

        $events = Event::query()
                    ->where('id', '>=', 1)
                    ->orderBy('created_at', 'desc')
                    ->with('airports.sceneries');  //Specifies that we want to bring the airports, as well as the sceneries


        if (!$request->input('showAll')) {
            $events = $events
                        ->where('dateEnd', '>=', Carbon::now());
        } else {
            if(!Auth::user()->admin) return response(['error' => 'admin.noAdmin'], 403);
        }

        $perPage = (int)$request->query('perPage', 5,);

        if ($request->query('status')) {
            $events->where('status', $request->query('status'));
        }

        return $this->paginationService->transform($events->paginate($perPage > 25 ? 25 : $perPage));
    }

    public function getSingle($id)
    {
        $event = Event::where('id', $id)->with('airports.sceneries')->first();  //Returns a single Event from the database

        if(!$event || $event->has_ended && !Auth::user()->admin) return response(['error' => 'event.notFound'], 404);

        return $event;
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $this->validate($request, [
            'dateStart' => 'required|numeric',
            'dateEnd' => 'required|numeric|gt:dateStart',
            'eventName' => 'required|string|max:255',
            'privateSlots' => 'required|boolean',
            'status' => 'required|string',
            'atcBriefing' => 'url',
            'pilotBriefing' => 'url',
            'description' => 'required|string',
            'banner' => 'required|url',
            'atcBooking' => 'required|url',
            'type' => 'required|string',
        ]);

        $event = Event::find($id);

        if (!$event) {
            abort(404, 'event.notFound');
        }

        //$this->authorize('update', $event);

        if(!Auth::user()->admin)
            return abort(403, 'admin.notAdmin');

        $dateStart = new Carbon();
        $dateEnd = new Carbon();

        $dateStart->setTimestamp($request->input('dateStart'));
        $dateEnd->setTimestamp($request->input('dateEnd'));

        if($dateStart->diffInHours($dateEnd) > 10) {
            return abort(403, 'event.tooLong');
        }

        $event->fill([
            'division' => $user->division,
            'dateStart' => $dateStart->toDateTimeString(),
            'dateEnd' => $dateEnd->toDateTimeString(),
            'eventName' => $request->input('eventName'),
            'privateSlots' => $request->input('privateSlots'),
            'status' => $request->input('status'),
            'createdBy' => $user->id,
            'pilotBriefing' => $request->input('pilotBriefing'),
            'atcBriefing' => $request->input('atcBriefing'),
            'description' => $request->input('description'),
            'banner' => $request->input('banner'),
            'atcBooking' => $request->input('atcBooking'),
            'type' => $request->input('type'),
        ]);

        self::setAirports($event->id, $request->input('airports'));

        $event->save();
    }

    public function delete($id)
    {
        $event = Event::find($id);

        if (!$event) {
            abort(404, 'event.notFound');
        }

        $this->authorize('delete', $event);

        Event::where('id', $id)->delete();
    }
}
