<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\MultiWebchatSettingsRequest;
use App\Http\Requests\WebchatSettingsRequest;
use App\Http\Resources\WebchatSettingsResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use OpenDialogAi\Webchat\Casts\WebchatSettingsValueCast;
use OpenDialogAi\Webchat\WebchatSetting;

class WebchatSettingsController extends Controller
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
     * @return WebchatSettingsResourceCollection
     */
    public function index(): WebchatSettingsResourceCollection
    {
        return new WebchatSettingsResourceCollection(
            WebchatSetting::withCasts(['value' => WebchatSettingsValueCast::class])->get()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param WebchatSetting $webchatSetting
     * @return WebchatSetting
     */
    public function show(WebchatSetting $webchatSetting): WebchatSetting
    {
        return $webchatSetting;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param WebchatSettingsRequest $request
     * @param WebchatSetting $webchatSetting
     * @return Response
     */
    public function update(WebchatSettingsRequest $request, WebchatSetting $webchatSetting): Response
    {
        $webchatSetting->update(['value' => $request->get('value')]);
        return response()->noContent(200);
    }

    /**
     * @param MultiWebchatSettingsRequest $request
     * @return Response
     */
    public function multiUpdate(MultiWebchatSettingsRequest $request): Response
    {
        $value = json_decode($request->getContent(), true);

        DB::beginTransaction();

        try {
            foreach ($value as $val) {
                /** @var WebchatSetting $setting */
                $setting = WebchatSetting::where('name', $val['name'])->first();
                $setting->update(['value' => $val['value']]);
            }
            DB::commit();
            return response()->noContent(200);
        } catch (\Exception $error) {
            DB::rollback();
            return response($error, 400);
        }
    }
}
