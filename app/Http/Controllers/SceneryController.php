<?php

namespace App\Http\Controllers;

use App\Events\Event as EventsEvent;
use App\Models\Event;
use App\Models\Scenery;
use Illuminate\Http\Request;
use App\Services\PaginationService;

class SceneryController extends Controller
{
    private $paginationService;

    function __construct(PaginationService $paginationService)
    {
        $this->paginationService = $paginationService;
    }

    public function create(Request $request)
    {
        $this->validate($request, $this->getValidatorRules());

        $this->authorize('create', Scenery::class);

        $scenery = new Scenery();
        $scenery->fill($request->all());
        $scenery->save();
    }

    public function get(Request $request)
    {
        $this->authorize('getAll', Scenery::class);

        $scenaries = Scenery::where('id', '>=', 1);

        $perPage = (int)$request->query('perPage', 5);

        return $this->paginationService->transform($scenaries->paginate($perPage > 25 ? 25 : $perPage));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, $this->getValidatorRules());

        $scenery = Scenery::find($id);

        if (!$scenery) {
            abort(404, 'Scenery not founded');
        }

        $this->authorize('update', $scenery);

        $scenery->fill([
            'title' => $request->input('title'),
            'license' => $request->input('license'),
            'link' => $request->input('link'),
            'simulator' => $request->input('simulator'),
            'icao'      =>  $request->input('icao'),
        ]);

        $scenery->save();
    }

    public function getByEvent(String $eventId)
    {
        $airports = Event::where('id', $eventId)->first()->airports;
        $airports->each (function($item) { $item->sceneries; });

        return $airports;
    }

    public function delete(String $sceneryId)
    {
        $this->authorize('delete', Scenery::class);

        Scenery::where('id', $sceneryId)->delete();
    }

    private function getValidatorRules() {
        return [
            'title'     => 'required|string|max:255',
            'license'   => 'required|string|in:freeware,payware',
            'link'      => 'required|string|url',
            'simulator' => 'required|string|in:fs9,fsx,p3d,xp11,msfs',
            'icao'      => 'required|string|size:4',
        ];
    }
}
