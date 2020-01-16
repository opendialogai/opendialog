<?php

namespace App\Http\Controllers\API;

use App\GlobalContext;
use App\Http\Controllers\Controller;
use App\Http\Resources\GlobalContextCollection;
use App\Http\Resources\GlobalContextResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GlobalContextsController extends Controller
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
     * @return GlobalContextCollection
     */
    public function index(): GlobalContextCollection
    {
        /** @var GlobalContext $globalContexts */
        $globalContexts = GlobalContext::paginate(50);
        return new GlobalContextCollection($globalContexts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return GlobalContextResource|Response
     */
    public function store(Request $request)
    {
        /** @var GlobalContext $globalContext */
        $globalContext = GlobalContext::make($request->all());
        if ($error = $this->validateValue($globalContext)) {
            return response($error, 400);
        }

        $globalContext->save();

        return new GlobalContextResource($globalContext);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return GlobalContextResource
     */
    public function show($id): GlobalContextResource
    {
        /** @var GlobalContext $globalContext */
        $globalContext = GlobalContext::find($id);

        return new GlobalContextResource($globalContext);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int    $id
     * @return Response
     */
    public function update(Request $request, $id): Response
    {
        /** @var GlobalContext $globalContext */
        if ($globalContext = GlobalContext::find($id)) {
            $globalContext->fill($request->all());

            if ($error = $this->validateValue($globalContext)) {
                return response($error, 400);
            }

            $globalContext->save();
        }

        return response()->noContent(200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id): Response
    {
        if ($globalContext = GlobalContext::find($id)) {
            $globalContext->delete();
        }

        return response()->noContent(200);
    }

    /**
     * @param GlobalContext $globalContext
     * @return array|null
     */
    private function validateValue(GlobalContext $globalContext): ?array
    {
        if (strlen($globalContext->name) > 255) {
            return [
                'field' => 'name',
                'message' => 'The maximum length for global context name is 255.',
            ];
        }

        if (!$globalContext->name) {
            return [
                'field' => 'name',
                'message' => 'Global context name field is required.',
            ];
        }

        if (! isset($globalContext->value)) {
            return [
                'field' => 'name',
                'message' => 'Global context value field is required.',
            ];
        }

        return null;
    }
}
