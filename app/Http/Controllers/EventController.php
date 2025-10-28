<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventAirport;
use App\Services\PaginationService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

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

        if (!Auth::user()->admin)
            return abort(403, 'admin.notAdmin');


        try {
            $this->validate($request, [
                'atcBooking' => 'required|url',
                'atcBriefing' => 'url',
                'banner' => 'required|url',
                'dateStart' => 'required|numeric',
                'dateEnd' => 'required|numeric|gt:dateStart',
                'description' => 'required|string',
                'eventName' => 'required|string|max:255',
                'pilotBriefing' => 'url',
                'publicAccess' => 'boolean',
                'airports' => 'required|string',
                'type' => 'required|string|in:rfe,rfo,msa',
            ], [
                'atcBooking.required' => 'The ATC booking link is required.',
                'atcBooking.url' => 'The ATC booking link must be a valid URL.',
                'atcBriefing.url' => 'The ATC briefing link must be a valid URL.',
                'banner.required' => 'The banner image link is required.',
                'banner.url' => 'The banner image link must be a valid URL.',
                'dateStart.required' => 'The start date is required.',
                'dateStart.numeric' => 'The start date must be a numeric value.',
                'dateEnd.required' => 'The end date is required.',
                'dateEnd.numeric' => 'The end date must be a numeric value.',
                'dateEnd.gt' => 'The end date must be greater than the start date.',
                'description.required' => 'The description is required.',
                'description.string' => 'The description must be a string.',
                'eventName.required' => 'The event name is required.',
                'eventName.string' => 'The event name must be a string.',
                'eventName.max' => 'The event name may not be greater than 255 characters.',
                'pilotBriefing.url' => 'The pilot briefing link must be a valid URL.',
                'publicAccess.boolean' => 'The public access field must be a boolean value (true or false).',
                'airports.required' => 'The airports field is required.',
                'airports.string' => 'The airports field must be a string.',
                'type.required' => 'The type field is required.',
                'type.string' => 'The type field must be a string.',
                'type.in' => 'The type field must be one of the following values: rfe, rfo, msa.',
            ]);
        } catch (ValidationException $e) {
            return response([
                'error' => [
                    'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'messages' => $e->errors()
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = Auth::user();

        $dateStart = new Carbon();
        $dateEnd = new Carbon();

        $dateStart->setTimestamp($request->input('dateStart'));
        $dateEnd->setTimestamp($request->input('dateEnd'));

        if ($dateStart->diffInHours($dateEnd) > 10) {
            return abort(403, 'event.tooLong');
        }

        $event = new Event();

        $event->fill([
            'division' => $user->division,
            'dateStart' => $dateStart->toDateTimeString(),
            'dateEnd' => $dateEnd->toDateTimeString(),
            'eventName' => $request->input('eventName'),
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

        return response($event, 201);
    }

    /**
     * MINIMUM VIABLE PRODUCT
     *
     * TODO: IMPROVE THIS THING TO AVOID NECESSARY CALLS TO THE DATABASE
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

    /**
     * @throws AuthorizationException
     */
    public function delete($id): void
    {
        $event = Event::whereId($id);

        if (!$event) {
            abort(404, 'event.notFound');
        }

        $this->authorize('delete', $event);

        $event->delete();
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
            if (!Auth::user()->admin) return response(['error' => 'admin.noAdmin'], 403);
        }

        $perPage = (int)$request->query('perPage', 5);

        if ($request->query('status')) {
            $statusParam = $request->query('status');

            if (is_array($statusParam)) {
                $statuses = array_filter(array_map('trim', $statusParam));
            } else {
                $statuses = array_filter(array_map('trim', explode(',', $statusParam)));
            }

            if (count($statuses) === 1) {
                $events = $events->where('status', $statuses[0]);
            } elseif (count($statuses) > 1) {
                $events = $events->whereIn('status', $statuses);
            }
        }

        return $this->paginationService->transform($events->paginate($perPage > 25 ? 25 : $perPage));
    }

    public function getSingle($id)
    {
        $event = Event::where('id', $id)->with('airports.sceneries')->first();  //Returns a single Event from the database

        if (!$event || $event->has_ended && !Auth::user()->admin) return response(['error' => 'event.notFound'], 404);

        return $event;
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $this->validate($request, [
            'dateStart' => 'required|numeric',
            'dateEnd' => 'required|numeric|gt:dateStart',
            'eventName' => 'required|string|max:255',
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

        if (!Auth::user()->admin)
            return abort(403, 'admin.notAdmin');

        $dateStart = new Carbon();
        $dateEnd = new Carbon();

        $dateStart->setTimestamp($request->input('dateStart'));
        $dateEnd->setTimestamp($request->input('dateEnd'));

        if ($dateStart->diffInHours($dateEnd) > 10) {
            return abort(403, 'event.tooLong');
        }

        $event->fill([
            'division' => $user->division,
            'dateStart' => $dateStart->toDateTimeString(),
            'dateEnd' => $dateEnd->toDateTimeString(),
            'eventName' => $request->input('eventName'),
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
}
