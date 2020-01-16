<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationLog\ChatbotUser;
use OpenDialogAi\Core\RequestLog;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use Symfony\Component\Yaml\Yaml;

class StatisticsController extends Controller
{
    public function chatbotUsers(Request $request)
    {
        $labels = [];
        $values = [];

        $startDate = new \DateTime($request->query->get('startdate'));
        $endDate = new \DateTime($request->query->get('enddate'));
        $interval = new \DateInterval('P1D');
        $endDate->add($interval);

        $period = new \DatePeriod($startDate, $interval, $endDate);

        foreach ($period as $date) {
            $start = $date->format('Y-m-d');

            $end = clone($date);
            $end->add($interval)->format('Y-m-d');

            $labels[] = $date->format('Y-m-d');
            $values[] = ChatbotUser::where('created_at', '>', $start)
                ->where('created_at', '<', $end)
                ->get()->count();
        }

        $data = [
            'total' => array_sum($values),
            'labels' => $labels,
            'values' => $values,
        ];

        return $data;
    }

    public function requests(Request $request)
    {
        $labels = [];
        $values = [];

        $startDate = new \DateTime($request->query->get('startdate'));
        $endDate = new \DateTime($request->query->get('enddate'));
        $interval = new \DateInterval('P1D');
        $endDate->add($interval);

        $period = new \DatePeriod($startDate, $interval, $endDate);

        foreach ($period as $date) {
            $start = $date->format('Y-m-d');

            $end = clone($date);
            $end->add($interval)->format('Y-m-d');

            $labels[] = $date->format('Y-m-d');
            $values[] = RequestLog::where('microtime', '>', $start)
                ->where('microtime', '<', $end)
                ->get()->count();
        }

        $data = [
            'total' => array_sum($values),
            'labels' => $labels,
            'values' => $values,
        ];

        return $data;
    }

    public function conversations()
    {
        return [
            'value' => Conversation::count(),
        ];
    }

    public function incomingIntents()
    {
        $incomingIntents = [];

        $conversations = Conversation::where('status', 'activated')->get();

        foreach ($conversations as $conversation) {
            $yaml = Yaml::parse($conversation->model);

            foreach ($yaml['conversation']['scenes'] as $scene) {
                if (isset($scene['intents'])) {
                    foreach ($scene['intents'] as $intent) {
                        if (isset($intent['u']['i'])) {
                            if (!in_array($intent['u']['i'], $incomingIntents)) {
                                $incomingIntents[] = $intent['u']['i'];
                            }
                        }
                    }
                }
            }
        }

        return [
            'value' => count($incomingIntents),
        ];
    }

    public function messageTemplates()
    {
        return [
            'value' => MessageTemplate::count(),
        ];
    }
}
