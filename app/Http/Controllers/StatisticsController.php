<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function users(Request $request)
    {
        $startDate = $request->query->get('startdate');
        $endDate = $request->query->get('enddate');

        $labels = [];
        $values = [];

        for ($i = 1; $i <= 15; $i++) {
            $labels[] = $i;
            $values[] = rand(2, 10);
        }

        $data = [
            'total' => rand(50, 100),
            'labels' => $labels,
            'values' => $values,
        ];

        return $data;
    }

    public function cost(Request $request)
    {
        $startDate = $request->query->get('startdate');
        $endDate = $request->query->get('enddate');

        return [
            'value' => '100%',
        ];
    }
}
