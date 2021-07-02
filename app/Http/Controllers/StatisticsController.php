<?php

namespace App\Http\Controllers;

use App\Stats\Helper;
use Illuminate\Http\Request;
use OpenDialogAi\ConversationLog\ChatbotUser;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\RequestLog;

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

        $data = Helper::setIntervals([
            'total' => array_sum($values),
            'labels' => $labels,
            'values' => $values,
        ]);

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

        $data = Helper::setIntervals([
            'total' => array_sum($values),
            'labels' => $labels,
            'values' => $values,
        ]);

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

    public function messageTemplates()
    {
        $totalMessages = 0;
        $this->getActiveScenarios()->each(function (Scenario $scenario) use (&$totalMessages) {
            ConversationDataClient::getAllConversationsByScenario($scenario)
                ->each(function (Conversation $conversation) use (&$totalMessages) {
                    $conversation->getScenes()->each(function (Scene $scene) use (&$totalMessages) {
                        $scene->getTurns()->each(function (Turn $turn) use (&$totalMessages) {
                            $turn->getRequestIntents()->each(function (Intent $intent) use (&$totalMessages) {
                                $totalMessages += $intent->getMessageTemplates()->count();
                            });

                            $turn->getResponseIntents()->each(function (Intent $intent) use (&$totalMessages) {
                                $totalMessages += $intent->getMessageTemplates()->count();
                            });
                        });
                    });
                });
        });

        return [
            'value' => $totalMessages,
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
