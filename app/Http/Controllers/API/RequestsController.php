<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequestCollection;
use App\Http\Resources\RequestResource;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Http\Request;
use OpenDialogAi\Core\RequestLog;

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
     * @return RequestCollection
     */
    public function index(Request $request)
    {
        /** @var QueryBuilder $query */
        $query = RequestLog::orderByDesc('microtime')->with('responseLog');

        if ($request->url) {
            $query->where('url', 'like', '%' . $request->url . '%');
        }
        if ($request->source_ip) {
            $query->where('source_ip', $request->source_ip);
        }
        if ($request->user_id) {
            $query->where('user_id', 'like', '%' . $request->user_id . '%');
        }
        if ($request->http_status) {
            $http_status = $request->http_status;

            $query->whereHas('responseLog', function ($query) use ($http_status) {
                $query->where('http_status', $http_status);
            });
        }

        $requestLogs = $query->paginate(50);

        return new RequestCollection($requestLogs);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return RequestResource
     */
    public function show($id)
    {
        $requestLog = RequestLog::where('request_id', $id)->with('responseLog')->first();

        if ($requestLog) {
            return new RequestResource($requestLog);
        }

        return response()->noContent(404);
    }
}
