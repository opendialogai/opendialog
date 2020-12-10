<?php


namespace App\ImportExportHelpers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

abstract class BaseImportExportHelper
{
    const SPECIFICATION_DISK_NAME = 'specification';

    /**
     * @return Filesystem
     */
    public static function getDisk(): Filesystem
    {
        return Storage::disk(self::SPECIFICATION_DISK_NAME);
    }
}
