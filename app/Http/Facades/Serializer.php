<?php


namespace App\Http\Facades;

use App\Http\Serializers\OpenDialogSerializer;
use Illuminate\Support\Facades\Facade;

class Serializer extends Facade
{

    /**
     * @method static function serialize($data, string $format, array $context = []): string
     * @method static function deserialize($data, string $type, string $format, array $context = [])
     * @method static function getSerializer(): Serializer
     **/
    protected static function getFacadeAccessor()
    {
        return OpenDialogSerializer::class;
    }
}
