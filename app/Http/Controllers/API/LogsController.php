<?php

namespace App\Http\Controllers\API;

use App\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\LogCollection;
use App\Http\Resources\LogResource;

class LogsController extends Controller
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
     * @return LogCollection
     */
    public function index(): LogCollection
    {
        /** @var Log $logs */
        $logs = Log::paginate(50);
        return new LogCollection($logs);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return LogResource
     */
    public function show($id): LogResource
    {
        /** @var Log $log */
        $log = Log::find($id);

        return new LogResource($log);
    }
}
