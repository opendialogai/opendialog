<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OutgoingIntentCollection;
use App\Http\Resources\OutgoingIntentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class OutgoingIntentsController extends Controller
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
     * @param Request $request
     * @return OutgoingIntentCollection
     */
    public function index(Request $request): OutgoingIntentCollection
    {
        $filterMessageContent = $request->get('filterMessageContent');
        $filterIntents = $request->get('filterIntents');

        $query = OutgoingIntent::query();

        if ($filterIntents) {
            $query->where('name', 'like', '%' . $filterIntents . '%');
        }

        if ($filterMessageContent) {
            $query->whereHas('messageTemplates', function ($subQuery) use ($filterMessageContent) {
                $subQuery->where('message_markup', 'like', '%' . $filterMessageContent . '%');
            });

            $query->with('messageTemplates');
        }

        /** @var OutgoingIntent $outgoingIntents */
        $outgoingIntents = $query->paginate(50);

        foreach ($outgoingIntents as $outgoingIntent) {
            $outgoingIntent->makeVisible('id');
        }

        return new OutgoingIntentCollection($outgoingIntents);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return OutgoingIntentResource
     */
    public function store(Request $request): OutgoingIntentResource
    {
        /** @var OutgoingIntent $outgoingIntent */
        $outgoingIntent = OutgoingIntent::make($request->all());

        if ($error = $this->validateValue($outgoingIntent)) {
            return response($error, 400);
        }

        $outgoingIntent->save();

        $outgoingIntent->makeVisible('id');

        return new OutgoingIntentResource($outgoingIntent);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return OutgoingIntentResource
     */
    public function show($id): OutgoingIntentResource
    {
        /** @var OutgoingIntent $outgoingIntent */
        $outgoingIntent = OutgoingIntent::find($id);

        $outgoingIntent->makeVisible('id');

        return new OutgoingIntentResource($outgoingIntent);
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
        /** @var OutgoingIntent $outgoingIntent */
        if ($outgoingIntent = OutgoingIntent::find($id)) {
            $outgoingIntent->fill($request->all());

            if ($error = $this->validateValue($outgoingIntent)) {
                return response($error, 400);
            }

            $outgoingIntent->save();
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
        if ($outgoingIntent = OutgoingIntent::find($id)) {
            $outgoingIntent->delete();
        }

        return response()->noContent(200);
    }


    /**
     * @param OutgoingIntent $outgoingIntent
     * @return array|null
     */
    private function validateValue(OutgoingIntent $outgoingIntent): ?array
    {
        if (strlen($outgoingIntent->name) > 255) {
            return [
                'field' => 'name',
                'message' => 'The maximum length for outgoing intent name is 255.',
            ];
        }

        if (!$outgoingIntent->name) {
            return [
                'field' => 'name',
                'message' => 'Outgoing intent name field is required.',
            ];
        }

        return null;
    }
}
