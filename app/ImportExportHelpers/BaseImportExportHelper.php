<?php


namespace App\ImportExportHelpers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

abstract class BaseImportExportHelper
{
    const SPECIFICATIONS_DISK_NAME = 'specifications';

    /**
     * @return Filesystem
     */
    public static function getDisk(): Filesystem
    {
        return Storage::disk(self::SPECIFICATIONS_DISK_NAME);
    }
}
