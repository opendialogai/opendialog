<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenDialogAi\AttributeEngine\DynamicAttribute;

class DynamicAttributeCollection extends ResourceCollection
{
    /**
     * Transform the collection into a dictionary of id => type pairs.
     *
     * @param Collection $collection
     * @return array
     */
    public static function toDictionary(Collection $collection): array
    {
        $keyed = $collection->mapWithKeys(function ($attr) {
            return [$attr['attribute_id'] => $attr['attribute_type']];
        });
        return $keyed->all();
    }

    /**
     * Build a DynamicAttributeCollection
     * from a dictionary of id => type pairs.
     *
     * @return DynamicAttributeCollection
     */
    public static function fromDictionary(array $dict): DynamicAttributeCollection
    {
        $array = [];
        foreach ($dict as $id => $type) {
            $array[] = DynamicAttribute::make(['attribute_id' => $id, 'attribute_type' => $type]);
        }

        return DynamicAttributeCollection::make($array);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
