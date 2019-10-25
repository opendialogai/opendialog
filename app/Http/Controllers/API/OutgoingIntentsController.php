<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OutgoingIntentCollection;
use App\Http\Resources\OutgoingIntentResource;
use Illuminate\Http\Request;
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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $outgoingIntents = OutgoingIntent::paginate(50);

        foreach ($outgoingIntents as $outgoingIntent) {
            $outgoingIntent->makeVisible('id');
        }

        return new OutgoingIntentCollection($outgoingIntents);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $outgoingIntent = OutgoingIntent::find($id);

        $outgoingIntent->makeVisible('id');

        return new OutgoingIntentResource($outgoingIntent);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
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
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($outgoingIntent = OutgoingIntent::find($id)) {
            $outgoingIntent->delete();
        }

        return response()->noContent(200);
    }

    /**
     * @param OutgoingIntent $outgoingIntent
     * @return string
     */
    private function validateValue(OutgoingIntent $outgoingIntent)
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
