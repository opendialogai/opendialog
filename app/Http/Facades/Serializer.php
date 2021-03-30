<?php


namespace App\Http\Facades;

use App\Http\Serializers\ConversationSerializer;
use Illuminate\Support\Facades\Facade;

/**
 * @method static serialize($data, string $format, array $context = []): string
 * @method static normalize($data, string $format, array $context = []): array
 * @method static deserialize($data, string $type, string $format, array $context = [])
 * @method static denormalize($data, string $type, string $format, array $context = [])
 * @method static getSerializer(): Serializer
 **/
class Serializer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ConversationSerializer::class;
    }
}
