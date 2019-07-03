<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenDialogAi\ConversationBuilder\Conversation;

class ConversationController extends Controller
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
        return Conversation::find($id);
    }

    public function viewAll()
    {
        return Conversation::all();
    }

    public function update(Request $request, $id)
    {
        return response()->setStatusCode(200);
    }

    public function delete(Request $request, $id)
    {
        return response()->setStatusCode(200);
    }
}
