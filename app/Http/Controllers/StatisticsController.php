<?php

namespace App\Http\Controllers;

class StatisticsController extends Controller
{
    public function users()
    {
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

    public function cost()
    {
        return [
            'value' => '100%',
        ];
    }
}
