<?php


namespace App\Console\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static serialize($data, string $format, array $context = []): string
 * @method static normalize($data, string $format, array $context = []): array
 * @method static deserialize($data, string $type, string $format, array $context = [])
 * @method static denormalize($data, string $type, string $format, array $context = [])
 * @method static getSerializer(): Serializer
 * @method static decode($data, string $format, array $context = [])
 **/
class ImportExportSerializer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Console\Serializers\ImportExportSerializer::class;
    }
}
