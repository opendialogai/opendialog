<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;
use Timothyasp\Color\Color;

class WebchatSetting extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'OpenDialogAi\Webchat\WebchatSetting';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * The human-friendly label.
     */
    public static function label()
    {
        return 'Webchat Settings';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $array = [
            Text::make('Name')->sortable()->withMeta([
                'extraAttributes' => [
                    'disabled' => true
                ]
            ]),
            // Create value fields conditionally according to type.
            Text::make('Value')->canSee(function () {
                return $this->type === 'string';
            }),
            Color::make('Value')->canSee(function () {
                return $this->type === 'colour';
            }),
            Number::make('Value')->canSee(function () {
                return $this->type === 'number';
            }),
            Boolean::make('Value')->canSee(function () {
                return $this->type === 'boolean';
            }),
            Code::make('Value')->canSee(function () {
                return $this->type === 'map';
            })->language('application/json'),
            Text::make('Value')->canSee(function () {
                if ($this->type === 'map') {
                    // \Log::debug($this->value);
                    // \Log::debug(print_r($this,1));
                    return true;
                }
                return false;
            })->onlyOnIndex()->withMeta(['value' => $this->printMap($this->value, $this->type)]),
            Text::make('Value')->canSee(function () {
                return $this->type === 'object';
            })->onlyOnIndex()->withMeta(['value' => '<parent>']),

            // Define relationships.
            BelongsTo::make('Parent', 'parent', WebchatSetting::class)->nullable()->canSee(function () {
                // Uncomment to show on child rows.
                // return $this->parent_id !== NULL;
                return false;
            }),
        ];

        if ($this->children()->count() > 0) {
            $array[] = HasMany::make('Children', 'children', WebchatSetting::class)->nullable();
        }

        return $array;
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        // Hide children from the main index.
        if (!$request['relationshipType']) {
            return $query->whereNull('parent_id');
        }
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Pretty-print and truncate json values for the index.
     */
    private function printMap($value, $type)
    {
        $output = '';
        if ($type === 'map' && !empty($value) && $json = json_decode($value, true)) {
            $items = [];
            foreach ($json as $key => $item) {
                if (is_numeric($key)) {
                    $items[] = (string) $item;
                } else {
                    $items[] = $key . ': ' . (string) $item;
                }
            }
            $output = implode(', ', $items);
            if (strlen($output) > 50) {
                $output = substr($output, 0, 50) . '...';
            }
        }
        return $output;
    }
}
