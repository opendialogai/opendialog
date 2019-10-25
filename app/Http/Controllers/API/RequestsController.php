<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenDialogAi\Core\RequestLog;
use OpenDialogAi\Core\ResponseLog;

class RequestsController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $requests = [];

        $requestLogs = RequestLog::orderByDesc('microtime')->paginate(50);

        foreach ($requestLogs as $requestLog) {
            $responseLog = ResponseLog::where('request_id', $requestLog->request_id)->first();

            $requests[] = [
                'requestLog' => $requestLog->toArray(),
                'responseLog' => ($responseLog) ? $responseLog->toArray() : false,
            ];
        }

        $paginated = $requestLogs->toArray();

        return [
            'data' => $requests,
            'meta' => Arr::except($paginated, [
                'data',
                'first_page_url',
                'last_page_url',
                'prev_page_url',
                'next_page_url',
            ]),
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $requestLog = RequestLog::where('request_id', $id)->first();
        $responseLog = ResponseLog::where('request_id', $id)->first();

        if ($requestLog) {
            return [
                'requestLog' => $requestLog->toArray(),
                'responseLog' => ($responseLog) ? $responseLog->toArray() : false,
            ];
        }

        return response()->noContent(404);
    }
}
