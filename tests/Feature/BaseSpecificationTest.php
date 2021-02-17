<?php


namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\AbstractAdapter;
use Tests\TestCase;

abstract class BaseSpecificationTest extends TestCase
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;

    public function setUp(): void
    {
        $this->markTestSkipped();

        parent::setUp();

        $this->setupFakeSpecificationDisk();
    }


    protected function setupFakeSpecificationDisk(): void
    {
        Artisan::call(
            'schema:init',
            [
                '--yes' => true
            ]
        );

        $this->disk = Storage::fake('specification');

        /** @var AbstractAdapter $diskAdapter */
        $diskAdapter = $this->disk->getAdapter();
        File::copyDirectory(base_path('tests/specification'), $diskAdapter->getPathPrefix());
    }
}
