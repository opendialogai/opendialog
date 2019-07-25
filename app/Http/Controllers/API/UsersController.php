<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class UsersController extends Controller
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
        return new UserCollection(User::paginate(50));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = User::make($request->all());

        if ($error = $this->validateValue($user)) {
            return response($error, 400);
        }

        $user->password = Hash::make(str_random(8));
        $user->save();

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return new UserResource(User::find($id));
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
        if ($user = User::find($id)) {
            if ($user->phone_number) {
                $user->phone_number = '+' . $user->phone_country_code . ' ' . $user->phone_number;
            }

            $user->fill($request->all());

            if ($error = $this->validateValue($user)) {
                return response($error, 400);
            }

            $user->save();

            return response()->noContent(200);
        }

        return response()->noContent(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($user = User::find($id)) {
            $user->delete();
            return response()->noContent(200);
        }

        return response()->noContent(404);
    }

    /**
     * @param User $user
     * @return string
     */
    private function validateValue(User $user)
    {
        if (empty($user->name)) {
            return [
                'field' => 'name',
                'message' => 'User name field is required.',
            ];
        }

        if (empty($user->email)) {
            return [
                'field' => 'email',
                'message' => 'User email field is required.',
            ];
        }

        if (empty($user->phone_number)) {
            return [
                'field' => 'phone_number',
                'message' => 'User phone number field is required.',
            ];
        }

        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return [
                'field' => 'email',
                'message' => 'Enter a valid email.',
            ];
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $phoneUtil->parse($user->phone_number);
        } catch (NumberParseException $e) {
            return [
                'field' => 'phone_number',
                'message' => 'Enter a valid phone number with prefix.',
            ];
        }

        return null;
    }
}
