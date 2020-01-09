<?php

namespace App\Http\Controllers\API;

use App\Warning;
use App\Http\Controllers\Controller;
use App\Http\Resources\WarningCollection;
use App\Http\Resources\WarningResource;

class WarningsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return WarningCollection
     */
    public function index(): WarningCollection
    {
        /** @var Warning $warnings */
        $warnings = Warning::paginate(50);
        return new WarningCollection($warnings);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return WarningResource
     */
    public function show($id): WarningResource
    {
        /** @var Warning $warning */
        $warning = Warning::find($id);

        return new WarningResource($warning);
    }
}
