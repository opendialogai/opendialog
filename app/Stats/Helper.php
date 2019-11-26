<?php

namespace App\Stats;

use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ConversationLog\Message;

abstract class Helper
{
    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public static function getDates(Request $request): array
    {
        $startDate = new DateTime($request->query->get('startdate'));
        $endDate = new DateTime($request->query->get('enddate'));

        $endDate->add(new DateInterval('P1D'));
        return array($startDate, $endDate);
    }

    public static function getCounts(Request $request, $query, $unique = false, $dateField = 'created_at')
    {
        try {
            list($startDate, $endDate) = self::getDates($request);
        } catch (\Exception $e) {
            Log::error(sprintf('Error trying to get stats dates - %s', $e->getMessage()));
            list($startDate, $endDate) = [new DateTime(), new DateTime()];
        }

        $query->where($dateField, '>=', $startDate)
            ->where($dateField, '<', $endDate);

        $query->select('user_id');

        if ($unique) {
            $query->distinct();
        }

        return ['value' => count($query->get())];
    }

    /**
     * Gets the count
     * @param Request $request
     * @param $intents
     * @param bool $unique
     * @return array
     */
    public static function getIntentCounts(Request $request, $intents, $unique = false)
    {
        /** @var Builder $query */
        $query = Message::containingIntents($intents)
            ->where('type', 'button')
            ->where('author', 'them');

        return self::getCounts($request, $query, $unique);
    }

    public static function getGraphCount(Request $request, $query)
    {
        $startDate = new DateTime($request->query->get('startdate'));
        $endDate = (new DateTime($request->query->get('enddate')))->add(new DateInterval('P1D'));

        $interval = $startDate->diff($endDate);

        $labels = [];
        $values = [];

        if ($interval->m == 0) {
            $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

            foreach ($period as $date) {
                $clone = clone($query);
                $end = (clone($date))->add(new DateInterval('P1D'));

                $values[] = $clone->where('created_at', '>=', $date)->where('created_at', '<', $end)->count();
                $labels[] = $date->format('Y-m-d');
            }
        } else {
            $period = new DatePeriod($startDate, new DateInterval('P2D'), $endDate);

            foreach ($period as $date) {
                $clone = clone($query);
                $end = (clone($date))->add(new DateInterval('P2D'));

                $values[] = $clone->where('created_at', '>=', $date)->where('created_at', '<', $end)->count();
                $labels[] = $date->format('Y-m-d') . ' / ' .
                  (clone($date))->add(new DateInterval('P1D'))->format('Y-m-d');
            }
        }

        $data = [
            'total' => array_sum($values),
            'labels' => $labels,
            'values' => $values,
        ];

        return $data;
    }
}
