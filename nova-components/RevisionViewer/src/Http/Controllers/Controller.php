<?php

namespace App\RevisionViewer\Http\Controllers;

use App\User;
use Phalcon\Diff;
use Phalcon\Diff\Renderer\Html\SideBySide;
use Spatie\Activitylog\Models\Activity;

class Controller
{
    public function renderRevisions($id)
    {
        $changes = [];

        // $conversation = Conversation::find($id);
        $activities = Activity::where('subject_id', $id)
            ->where('log_name', 'conversation_log')
            ->orderBy('created_at', 'desc')
            ->get();

        $lastDate = false;
        $lastUser = false;

        foreach ($activities as $activity) {
            if (!count($activity->changes['attributes']) || empty($activity->changes['old'])) {
                continue;
            }

            $date = $activity->created_at->format('Y-m-d h:i:s A');

            $renderer = new SideBySide();

            $user = User::find($activity->causer_id);

            $diffs = [];
            $updates = [];
            foreach ($activity->changes['attributes'] as $type => $value) {
                $diff = new Diff(explode("\n", $activity->changes['old'][$type]), explode("\n", $value));

                $format = '<div class="label">' . ucfirst($type) . ':</div>' . $diff->render($renderer);

                $diffs[] = $format;
                $updates[] = ucfirst($type);
            }

            $changes[] = [
                'date' => $date,
                'user' => $user->name,
                'updates' => implode(' & ', $updates),
                'oldValue' => $activity->changes['old'],
                'newValue' => $activity->changes['attributes'],
                'format' => implode('', $diffs),
            ];
        }

        return $changes;
    }
}
