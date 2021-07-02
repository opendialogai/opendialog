<?php

namespace Tests\Feature;

use App\Stats\Helper;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenDialogAi\ConversationLog\Message;
use Tests\TestCase;

class StatsHelperTest extends TestCase
{
    private $start;
    private $end;
    private $request;

    public function setup(): void
    {
        parent::setUp();

        $this->start = new DateTime('last monday');
        $this->end = new DateTime('next monday');

        $this->request = new Request([
            'startdate' => $this->start->format('Y-m-d'),
            'enddate' => $this->end->format('Y-m-d'),
        ]);

        $message = new Message([
            'user_id' => Str::random(20),
            'author' => 'them',
            'message' => Str::random(20),
            'message_id' => Str::random(20),
            'type' => 'button',
            'microtime' => date('Y-m-d') . ' 10:35:06.340100',
            'intents' => ['country_response'],
            'conversation' => 'welcome',
            'scene' => 'opening_scene',
        ]);
        $message->save();
    }

    public function testStatsHelperGetDates()
    {
        $end = clone($this->end);
        $end->modify('+1 day');

        list($startDate, $endDate) = Helper::getDates($this->request);

        $this->assertEquals($startDate->format('Y-m-d'), $this->start->format('Y-m-d'));
        $this->assertEquals($endDate->format('Y-m-d'), $end->format('Y-m-d'));
    }

    public function testStatsHelperGetCounts()
    {
        $query = Message::containingIntent('country_response')
            ->select('user_id')
            ->distinct();

        $counts = Helper::getCounts($this->request, $query);

        $this->assertEquals($counts['value'], 1);
    }

    public function testStatsHelperGetIntentCounts()
    {
        $intents = ['country_response'];

        $intentCounts = Helper::getIntentCounts($this->request, $intents);

        $this->assertEquals($intentCounts['value'], 1);
    }

    public function testStatsHelperGetGraphCount()
    {
        $end = clone($this->end);
        $end->modify('+1 day');

        $query = Message::containingIntent('country_response')
            ->select('user_id')
            ->distinct();

        $graphCount = Helper::getGraphCount($this->request, $query);

        $period = new DatePeriod($this->start, new DateInterval('P1D'), $end);

        $labels = [];
        $values = [];
        foreach ($period as $date) {
            $labels[] = $date->format('Y-m-d');
            $values[] = ($date->format('Y-m-d') == date('Y-m-d')) ? 1 : 0;
        }

        $this->assertEquals($graphCount['total'], 1);
        $this->assertEquals($graphCount['labels'], $labels);
        $this->assertEquals($graphCount['values'], $values);
    }

    public function testSetIntervals()
    {
        $startDate = Carbon::make('2020-01-01');
        $endDate = Carbon::make('2020-02-01');

        $daily = Helper::setIntervals($this->setUpData($startDate, $endDate));
        $this->assertCount(31, $daily['labels']);
        $this->assertCount(31, $daily['values']);
        $this->assertEquals(31, $daily['total']);

        $endDate = Carbon::make('2020-03-01');

        $weekly = Helper::setIntervals($this->setUpData($startDate, $endDate));
        $this->assertCount(9, $weekly['labels']);
        $this->assertCount(9, $weekly['values']);
        $this->assertEquals(60, $weekly['total']);

        $endDate = Carbon::make('2020-06-01');

        $monthly = Helper::setIntervals($this->setUpData($startDate, $endDate));
        $this->assertCount(5, $monthly['labels']);
        $this->assertCount(5, $monthly['values']);
        $this->assertEquals(152, $monthly['total']);
    }

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array[]
     */
    private function setUpData($startDate, $endDate): array
    {
        $startDate = clone($startDate);
        $startDate->subDay();

        $data = [
            'labels' => [],
            'values' => [],
            'total' => 0
        ];

        while ($startDate->diffInDays($endDate) > 1) {
            $data['labels'][] = $startDate->addDays(1)->format('Y-m-d');
            $data['values'][] = 1;
            $data['total'] += 1;
        }
        return $data;
    }
}
