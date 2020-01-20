<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
     * @return UserCollection
     */
    public function index(): UserCollection
    {
        return new UserCollection(User::paginate(50));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return UserResource
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = User::make($request->all());

        if ($error = $this->validateValue($user)) {
            return response($error, 400);
        }

        $user->password = Hash::make(Str::random(8));
        $user->save();

        return new UserResource($user);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return UserResource
     */
    public function show($id): UserResource
    {
        return new UserResource(User::find($id));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     * @return Response
     */
    public function update(Request $request, $id): Response
    {
        /** @var User $user */
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
     * @param int $id
     * @return Response
     */
    public function destroy($id): Response
    {
        /** @var User $user */
        if ($user = User::find($id)) {
            try {
                $user->delete();
                return response()->noContent(200);
            } catch (\Exception $e) {
                Log::error(sprintf('Error deleting user - %s', $e->getMessage()));
                return response('Error creating user', 500);
            }
        }

        return response()->noContent(404);
    }

    /**
     * @param User $user
     * @return array
     */
    private function validateValue(User $user): ?array
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

        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return [
                'field' => 'email',
                'message' => 'Enter a valid email.',
            ];
        }

        if ($user->phone_number) {
            $phoneUtil = PhoneNumberUtil::getInstance();
            try {
                $phoneUtil->parse($user->phone_number);
            } catch (NumberParseException $e) {
                return [
                    'field' => 'phone_number',
                    'message' => 'Enter a valid phone number with prefix.',
                ];
            }
        }

        return null;
    }
}
