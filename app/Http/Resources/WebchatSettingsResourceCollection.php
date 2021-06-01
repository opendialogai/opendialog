<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

class WebchatSettingsResourceCollection extends ResourceCollection
{
    public static $wrap = null;

    public function toArray($request)
    {
        $response = [];

        $this->collection->groupBy(['section', 'subsection'])->each(function ($items, $title) use (&$response) {
            if ($title) {
                $response[] = $this->formatSection($title, $items);
            }
        });

        return $response;
    }

    /**
     * Sets the section title and the formatted subsection
     *
     * @param $title
     * @param $items
     * @return array
     */
    private function formatSection($title, $items): array
    {
        $topLevel = [
            'section' => $title,
            'children' => $this->formatSubsection($items)
        ];

        return $topLevel;
    }

    /**
     * Formats the subsection with title and formatted children
     *
     * @param $items
     * @return array
     */
    private function formatSubsection($items): array
    {
        $formatted = [];

        $items->each(function ($children, $subsection) use (&$formatted) {
            $formatted[] = [
                'subsection' => $subsection,
                'children' => $this->formatChildren($children)
            ];
        });

        return $formatted;
    }

    /**
     * Formats children webchat settings by grouping together siblings
     *
     * @param Collection $items
     * @return array
     */
    private function formatChildren(Collection $items): array
    {
        $formatted = [];
        $processed = [];

        $items->each(function ($item) use (&$formatted, $items, &$processed) {
            if (in_array($item->id, $processed)) {
                return;
            }
            if (!$item->sibling) {
                $formatted[] = [$item];
            } else {
                $sibling = $items->filter(fn ($i) => ($i->id == $item->sibling))->first();
                $formatted[] = [$sibling, $item];
                $processed[] = $sibling->id;
            }
        });

        return $formatted;
    }
}
