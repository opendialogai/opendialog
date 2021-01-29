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
     * Upload a JSON object of { attribute_id: attribute_type } pairs
     * as DynamicAttributes.
     *
     * @param Request $request
     *
     * @return Response
     */
    public static function upload(Request $request): Response
    {
        $data = $request->all();
        if ($error = self::validateImport($data)) {
            return response($error, 400);
        }

        try {
            DB::transaction(function () use ($data) {
                foreach ($data as $id => $type) {
                    DynamicAttribute::create(['attribute_id' => $id, 'attribute_type' => $type]);
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
     * Validate uploaded/imported DynamicAttributes
     *
     * @param array $data
     *
     * @return array|null
     */
    public static function validateImport(array $data): ?array
    {
        foreach ($data as $attribute_id => $attribute_type) {
            if (!is_string($attribute_id) || !is_string($attribute_type)) {
                return [
                    'message' => 'Must be a JSON object of the form:
 { <attribute_id>: attribute.<component>.<attribute_type>, ... }',
                ];
            }
        }

        $invalidIds = array_filter(
            array_keys($data),
            fn($attribute_id) => !DynamicAttribute::isValidId($attribute_id)
        );
        if (!empty($invalidIds)) {
            return [
                'ids' => $invalidIds,
                'message' => 'Invalid attribute ids. All attribute Ids must be in snake_case',
            ];
        }

        $invalidTypes = array_filter(
            array_values($data),
            fn($attribute_type) => !DynamicAttribute::isValidType($attribute_type)
        );
        if (!empty($invalidTypes)) {
            return [
                'types' => $invalidTypes,
                'message' => 'Invalid attribute types. All attribute types must be in the following format:
 attribute.<component>.<type>',
            ];
        }

        $attribute_ids = array_keys($data);
        $existing = DynamicAttribute::whereIn('attribute_id', $attribute_ids);
        /* Todo: Check for existing attributes in config */
        if ($existing->count() > 0) {
            return [
                'ids' => $existing->get()->map(fn($item) => $item->attribute_id),
                'message' => 'Some ids are already in use.',
            ];
        }

        $types = array_values($data);
        // Todo: Check if the types exist.

        return null;
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

        if (DynamicAttribute::where('attribute_id', $dynamicAttribute->attribute_id)->exists()
            || false /* TODO: Check for existing attributes from config */) {
            return response([
                'field' => 'attribute_id',
                'message' => sprintf(
                    "Attribute ID '%s' is already in use.",
                    $dynamicAttribute->attribute_id
                ),
            ], 400);
        }

        try {
            $dynamicAttribute->save();
        } catch (QueryException $e) {
            return response(
                [
                    'attribute' => $dynamicAttribute->toJson(),
                    'message' => "Unexpected error occurred saving dynamic attribute",
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
        if (empty($dynamicAttribute->attribute_id)) {
            return [
                'field' => 'attribute_id',
                'message' => 'attribute_id field is required.',
            ];
        }

        if (empty($dynamicAttribute->attribute_type)) {
            return [
                'field' => 'attribute_type',
                'message' => 'attribute_type field is required.',
            ];
        }

        if ($dynamicAttribute->attribute_id) {
            if (!DynamicAttribute::isValidId($dynamicAttribute->attribute_id)) {
                return [
                    'field' => 'attribute_id',
                    'message' => 'attribute_id field must follow snake_case format.',
                ];
            }
        }

        if ($dynamicAttribute->attribute_type) {
            if (!DynamicAttribute::isValidType($dynamicAttribute->attribute_type)) {
                return [
                    'field' => 'attribute_type',
                    'message' => "attribute_type field must follow the format: 'attribute.<component_name>.<type>'",
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
     * @param int $id
     *
     * @return DynamicAttributeResource
     */
    public function show(int $id): DynamicAttributeResource
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
     * @param int $id
     *
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        if ($dynamicAttribute = DynamicAttribute::find($id)) {
            $dynamicAttribute->fill($request->all());

            $sameAttributeId = DynamicAttribute::where([
                ['attribute_id', $dynamicAttribute->attribute_id],
                ['id', '<>', $dynamicAttribute->id]
            ])->get();
            if ($sameAttributeId->count() > 0 || false /* TODO: Check for existing attributes from config */) {
                return response([
                    'field' => 'attribute_id',
                    'message' => sprintf(
                        "Attribute id '%s' is already in use.",
                        $dynamicAttribute->attribute_id
                    ),
                ], 400);
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
     * @param int $id
     *
     * @return Response
     */
    public function destroy(int $id): Response
    {
        if ($dynamicAttribute = DynamicAttribute::find($id)) {
            try {
                $dynamicAttribute->delete();
                return response()->noContent();
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
            $map[$dynamicAttribute->attribute_id] = $dynamicAttribute->attribute_type;
        }
        return new JsonResponse(
            $map,
            200,
            ['Content-Disposition' => 'attachment; filename="custom-attributes.json"']
        );
    }
}
