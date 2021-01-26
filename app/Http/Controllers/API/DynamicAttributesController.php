<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DynamicAttributeCollection;
use App\Http\Resources\DynamicAttributeResource;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $dynamicAttribute = DynamicAttribute::make($request->all());

        if ($error = $this->validateValue($dynamicAttribute)) {
            return response($error, 400);
        }

        if (DynamicAttribute::find($dynamicAttribute->id) || false /* TODO: Check for existing attributes from config */) {
            return response([
                'field' => 'id',
                'message' => sprintf(
                    "Id '%s' is already in use.",
                    $dynamicAttribute->id
                ),
            ], 400);
        }

        try {
            $dynamicAttribute->save();
        } catch (QueryException $e) {
            return response(
                [
                'attribute' => $dynamicAttribute->toJson(),
                'message' => "Unexpected occurred saving dynamic attribute",
                ],
                500
            );
        }

        return new DynamicAttributeResource($dynamicAttribute);
    }

    /**
     * @param DynamicAttribute $dynamicAttribute
     *
     * @return array|null
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

        if (false) {
            /* TODO: Check if type provided is a registered type */
        }

        return null;
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
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
     * @param Request $request
     * @param string $id
     *
     * @return Response
     */
    public function update(Request $request, string $id): Response
    {
        if ($dynamicAttribute = DynamicAttribute::find($id)) {
            $dynamicAttribute->fill($request->all());

            /* If we're changing an existing DynamicAttribute's id, check for duplicates */
            if ($dynamicAttribute->id !== $id) {
                if (DynamicAttribute::find($dynamicAttribute->id)
                    || false /* TODO: Check for existing attributes from config */) {
                    return response([
                        'field' => 'id',
                        'message' => sprintf(
                            "Id '%s' is already in use.",
                            $dynamicAttribute->id
                        ),
                    ], 400);
                }
            }

            if ($error = $this->validateValue($dynamicAttribute)) {
                return response($error, 400);
            }

            try {
                $dynamicAttribute->save();
                return response()->noContent();
            } catch (Exception $e) {
                return response(sprintf(
                    'Error saving updated DynamicAttribute %s',
                    $dynamicAttribute->id
                ), 500);
            }
        }

        return response()->noContent(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     *
     * @return Response
     */
    public function destroy(string $id): Response
    {
        if ($dynamicAttribute = DynamicAttribute::find($id)) {
            try {
                $dynamicAttribute->delete();
                return response()->noContent(200);
            } catch (Exception $e) {
                return response(sprintf(
                    'Error deleting DynamicAttribute %s',
                    $dynamicAttribute->id
                ), 500);
            }
        }

        return response()->noContent(404);
    }

    /**
     * Download all dynamic-attributes as JSON
     *
     * @return JsonResponse
     */
    public function download(): JsonResponse
    {
        $map = [];
        foreach (DynamicAttribute::all() as $dynamicAttribute) {
            $map[$dynamicAttribute->id] = $dynamicAttribute->type;
        }
        return new JsonResponse(
            $map,
            200,
            ['Content-Disposition' => 'attachment; filename="dynamic-attributes.json"']
        );
    }


    /**
     * Upload a JSON object of { attribute_id: attribute_type } pairs
     * as DynamicAttributes.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function upload(Request $request): Response
    {
        $data = $request->all();
        if ($error = $this->validateUpload($data)) {
            return response($error, 400);
        }

        try {
            DB::transaction(function () use ($data) {
                foreach ($data as $id => $type) {
                    DynamicAttribute::create(['id' => $id, 'type' => $type]);
                }
            });
            return response($data, 201);
        } catch (QueryException $e) {
            return response(
                "Unexpected occurred saving uploaded dynamic attributes.",
                500
            );
        }
    }

    /**
     * Validate uploaded DynamicAttributes
     *
     * @param array $data
     *
     * @return array|null
     */
    public function validateUpload(array $data): ?array
    {
        if (empty($data)) {
            return [
                'message' => 'Unable to parse upload body. Uploads must be a JSON object in of the form:
 { <attribute_id>: attribute.<component>.<attribute_type> }',
            ];
        }

        $invalidIds = array_filter(
            array_keys($data),
            fn ($id) => !DynamicAttribute::isValidId($id)
        );
        if (!empty($invalidIds)) {
            return [
                'ids' => $invalidIds,
                'message' => 'Invalid attribute ids in upload. All attribute Ids must be in snake_case',
            ];
        }

        $invalidTypes = array_filter(
            array_values($data),
            fn ($type) => !DynamicAttribute::isValidType($type)
        );
        if (!empty($invalidTypes)) {
            return [
                'types' => $invalidTypes,
                'message' => 'Invalid attribute types in upload. All attribute types must be in the following format:
 attribute.<component>.<type>',
            ];
        }

        $ids = array_keys($data);
        $existing = DynamicAttribute::whereIn('id', $ids);
        /* Todo: Check for existing attributes in config */
        if ($existing->count() > 0) {
            return [
                'ids' => $existing->get()->map(fn ($item) => $item->id),
                'message' => 'Some ids in upload are already in use.',
            ];
        }

        $types = array_values($data);
        // Todo: Check if the types exist.

        return null;
    }
}
