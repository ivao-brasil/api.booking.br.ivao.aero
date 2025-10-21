<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Event;
use App\Models\Slot;
use App\Services\HQAPIService;
use App\Services\PaginationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AircraftController extends Controller
{
    private $paginationService;

    function __construct(PaginationService $paginationService)
    {
        $this->paginationService = $paginationService;
    }

    public function get(Request $request)
    {
        $this->authorize('getAll', Aircraft::class);

        $aircraft = Aircraft::where('id', '>=', 1);

        $perPage = (int)$request->query('perPage', 5);

        return $this->paginationService->transform($aircraft->paginate($perPage > 25 ? 25 : $perPage));
    }

    //Gets the list of missing aircraft and their respective slot count
    public function getMissing()
    {
        //Authorizes
        $this->authorize('getAll', Aircraft::class);

        //Gets all valid slots
        $aircraft = Slot::all()->where('aircraft', '!=', null);

        //Eliminates all aircraft that is already registered into the system
        $aircraft = $aircraft->reject(function ($value) {
            return $value->aircraftData != null;
        });


        //Groups by aircraft
        $aircraft = $aircraft->groupBy('aircraft');

        //Replaces the HUGE list of aircraft with a count()
        $aircraft = $aircraft->map( function($value, $key){
            return $value->count();
        });

        //Don't worry, be 0!
        return $aircraft;
    }

    public function create(Request $request)
    {
        $this->validate($request, $this->getValidatorRules());
        $this->authorize('create', Aircraft::class);

        $aircraft = new Aircraft();
        $aircraft->fill($request->all());
        $aircraft->save();
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, $this->getValidatorRules());

        $aircraft = Aircraft::find($id);

        if (!$aircraft) {
            abort(404, 'aircraft.notFound');
        }

        $this->authorize('update', $aircraft);

        $aircraft->fill([
            'iata' => $request->input('iata'),
            'icao' => $request->input('icao'),
            'name' => $request->input('name'),
            'speed' => $request->input('speed')
        ]);

        $aircraft->save();
    }

    public function delete($id)
    {
        $this->authorize('delete', Aircraft::class);

        Aircraft::where('id', $id)->delete();
    }

    private function getValidatorRules() {
        return [
            'icao'      => 'required|string|max:4',
            'iata'      => 'required|string|max:3',
            'name'      => 'required|string|max:255',
            'speed'     => 'required|integer',
        ];
    }

}
