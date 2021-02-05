<?php


namespace App\ImportExportHelpers;

class DynamicAttributeImportExportHelper extends BaseImportExportHelper
{
    const RESOURCE_DIRECTORY = 'attributes';
    const FILE_EXTENSION = ".json";

    public static function overwrite(array $attrs, string $path)
    {
        self::getDisk()->put($path, json_encode($attrs, JSON_PRETTY_PRINT));
    }

    public static function exists(string $name)
    {
        $path = self::getFilePath($name);
        return self::getDisk()->exists($path);
    }

    public static function getFilePath(string $name): string
    {
        return self::RESOURCE_DIRECTORY . "/$name" . self::FILE_EXTENSION;
    }

    public static function getFileData(string $filePath): string
    {
        return self::getDisk()->get($filePath);
    }

    public static function importFromString(string $data): array
    {
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }

}
