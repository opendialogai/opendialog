<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class SpecificationController extends Controller
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

    public function import()
    {
        Artisan::call(
            'specification:import',
            [
                '--yes' => true
            ]
        );
    }

    public function export()
    {
        Artisan::call(
            'specification:export',
            [
                '--yes' => true
            ]
        );
    }
}
