<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    public function view($id)
    {
        return WebchatSetting::find($id);
    }

    public function viewAll()
    {
        return WebchatSetting::all();
    }

    public function update(Request $request, $id)
    {
        return response()->setStatusCode(200);
    }
}
