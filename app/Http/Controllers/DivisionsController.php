<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\PaginationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DivisionsController extends Controller
{
    private $paginationService;

    function __construct(PaginationService $paginationService)
    {
        $this->paginationService = $paginationService;
    }

    public function get()
    {
        return Division::get();
    }

    public function getSingle($divisionId)
    {
        $division = Division::where('id', $divisionId)->first();

        if (!$division || $division->has_ended) return response(['error' => 'division.notFound'], 404);

        return $division;
    }
}
