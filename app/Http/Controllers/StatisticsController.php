<?php

namespace App\Http\Controllers;

use App\Stats\Helper;
use Illuminate\Http\Request;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationLog\ChatbotUser;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\RequestLog;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use Symfony\Component\Yaml\Yaml;

class StatisticsController extends Controller
{
    public function chatbotUsers(Request $request)
    {
        $labels = [];
        $values = [];

        list($startDate, $endDate) = Helper::getDates($request);

        if ($value = Helper::getCache('chatbotUsers', $startDate, $endDate)) {
            return $value;
        }

        $interval = new \DateInterval('P1D');
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

        Helper::setCache('chatbotUsers', $data, $startDate, $endDate);

        return $data;
    }

    public function requests(Request $request)
    {
        $labels = [];
        $values = [];

        list($startDate, $endDate) = Helper::getDates($request);

        if ($value = Helper::getCache('requests', $startDate, $endDate)) {
            return $value;
        }

        $interval = new \DateInterval('P1D');
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

        Helper::setCache('requests', $data, $startDate, $endDate);

        return $data;
    }

    public function scenarios()
    {
        $totalActivatedScenarios = $this->getActiveScenarios()->count();

        return [
            'value' => $totalActivatedScenarios,
        ];
    }

    public function conversations()
    {
        $totalActivatedConversations = 0;
        $this->getActiveScenarios()->each(function (Scenario $scenario) use (&$totalActivatedConversations) {
            $totalActivatedConversations += $scenario->getConversations()->count();
        });

        return [
            'value' => $totalActivatedConversations,
        ];
    }

    public function incomingIntents()
    {
        if ($value = Helper::getCache('incomingIntents')) {
            return [
                'value' => $value,
            ];
        }

        $incomingIntents = [];

        $conversations = Conversation::where('status', ConversationNode::ACTIVATED)->get();

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

        $totalIncomingIntents = count($incomingIntents);
        Helper::setCache('incomingIntents', $totalIncomingIntents);

        return [
            'value' => $totalIncomingIntents,
        ];
    }

    public function messageTemplates()
    {
        if ($value = Helper::getCache('messageTemplates')) {
            return [
                'value' => $value,
            ];
        }

        $totalMessageTemplates = MessageTemplate::count();
        Helper::setCache('messageTemplates', $totalMessageTemplates);

        return [
            'value' => $totalMessageTemplates,
        ];
    }

    /**
     * @return mixed
     */
    public function getActiveScenarios()
    {
        return ConversationDataClient::getAllScenarios()->filter(function (Scenario $scenario) {
            return $scenario->isActive();
        });
    }
}
