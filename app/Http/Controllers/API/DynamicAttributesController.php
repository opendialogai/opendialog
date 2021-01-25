<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DynamicAttributeCollection;
use App\Http\Resources\DynamicAttributeResource;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\DynamicAttribute;

class DynamicAttributesController extends Controller
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
     * @return DynamicAttributeCollection
     */
    public function index(): DynamicAttributeCollection
    {
        return new DynamicAttributeCollection(DynamicAttribute::paginate(50));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        /*
            @var $dynamicAttribute DynamicAttribute
        */
        $dynamicAttribute = DynamicAttribute::make($request->all());

        if ($existing = DynamicAttribute::find($dynamicAttribute->id)) {
            return response([
                'field' => 'id',
                'message' => sprintf(
                    "ID '%s' is already in use.",
                    $dynamicAttribute->id
                ),
            ], 400);
        }

        if ($error = $this->validateValue($dynamicAttribute)) {
            return response($error, 400);
        }

        try {
            $dynamicAttribute->save();
        } catch (QueryException $e) {
            return response(
                "Unexpected occurred saving dynamic attribute.",
                500
            );
        }

        return new DynamicAttributeResource($dynamicAttribute);
    }

    /**
     * @param  DynamicAttribute  $dynamicAttribute
     *
     * @return array
     */
    private function validateValue(DynamicAttribute $dynamicAttribute): ?array
    {
        if (empty($dynamicAttribute->id)) {
            return [
                'field' => 'id',
                'message' => 'Attribute ID field is required.',
            ];
        }

        if (empty($dynamicAttribute->type)) {
            return [
                'field' => 'type',
                'message' => 'Type field is required.',
            ];
        }

        if ($dynamicAttribute->id) {
            if (!DynamicAttribute::isValidId($dynamicAttribute->id)) {
                return [
                    'field' => 'id',
                    'message' => 'ID field must follow snake_case format.',
                ];
            }
        }

        if ($dynamicAttribute->type) {
            if (!DynamicAttribute::isValidType($dynamicAttribute->type)) {
                return [
                    'field' => 'type',
                    'message' => "Type field must follow the format: 'attribute.<component_name>.<type>'",
                ];
            }
        }

        return null;
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     *
     * @return DynamicAttributeResource
     */
    public function show(string $id): DynamicAttributeResource
    {
        if ($dynamicAttribute = DynamicAttribute::find($id)) {
            return new DynamicAttributeResource($dynamicAttribute);
        }

        return response()->noContent(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  string  $id
     *
     * @return Response
     */
    public function update(Request $request, string $id): Response
    {
        /*
            @var DynamicAttribute $dynamicAttribute
        */
        if ($dynamicAttribute = DynamicAttribute::find($id)) {
            $dynamicAttribute->fill($request->all());

            if ($error = $this->validateValue($dynamicAttribute)) {
                return response($error, 400);
            }

            $dynamicAttribute->save();

            return response()->noContent();
        }

        return response()->noContent(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     *
     * @return Response
     */
    public function destroy(string $id): Response
    {
        /*
            @var DynamicAttribute $user
        */
        if ($dynamicAttribute = DynamicAttribute::find($id)) {
            try {
                $dynamicAttribute->delete();
                return response()->noContent(200);
            } catch (Exception $e) {
                Log::error(sprintf(
                    'Error deleting DynamicAttribute - %s',
                    $e->getMessage()
                ));
                return response(sprintf(
                    'Error deleting DynamicAttribute %s',
                    $dynamicAttribute->id
                ), 500);
            }
        }

        return response()->noContent(404);
    }
}
