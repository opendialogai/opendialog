<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how attachment configuration should be interpreted.
    | This allows for enabling legacy interpretation of Stapler configuration
    | for hasAttachedFile() calls.
    |
    */

    'config' => [
        // Available modes: 'paperclip' (default), 'stapler'
        'mode' => 'paperclip',
    ],

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    |
    | Configure how attached file information in stored on attachables.
    |
    */

    'model' => [

        // Mark which columns should be filled on the model by default.
        // These attributes are prefixed by <attribute name>_.
        'attributes' => [
            'size'         => true,
            'content_type' => true,
            'updated_at'   => true,
            'created_at'   => false,
            // JSON information about stored variants.
            'variants'     => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Settings for handling storage of uploaded files.
    |
    */

    'storage' => [

        // The Laravel storage disk to use.
        'disk' => 'paperclip',

        // Per disk, the base URL where attachments are stored at. If 'url' is set for the disk, this is not required.
        'base-urls' => [
            'paperclip' => config('app.url') . '/paperclip',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Path
    |--------------------------------------------------------------------------
    |
    | The storage path that uploaded files and variants for models are placed in.
    |
    */

    'path' => [

        // The class that generates the paths
        'interpolator' => \Czim\Paperclip\Path\Interpolator::class,

        // The path to the original file to be interpolated. This will also\
        // be used for variant paths if the variant key is unset.
        'original' => ':class/:id_partition/:attribute/:variant/:filename',

        // If the structure for variant filenames should differ from the
        // original, it may be defined here.
        'variant'  => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Variants
    |--------------------------------------------------------------------------
    |
    | Processed files may have any number of variants: versions of the file that
    | are resized, rotated, compressed, or whatever you can think of.
    |
    */

    // The default to use for the main URL
    'default-variant' => 'original',

    // Variant processing configuration
    'variants' => [

        'aliases' => [
            'auto-orient' => \Czim\FileHandling\Variant\Strategies\ImageAutoOrientStrategy::class,
            'resize'      => \Czim\FileHandling\Variant\Strategies\ImageResizeStrategy::class,
        ],

        // Set this to true to always merge in the default variants into any attachment configuration.
        // False only sets defaults if no variants are configured for an attachment;
        // true always merges them in (not overriding specifics by variant name).
        //
        // When this is enabled, it is possible to 'disable' the default variants by setting
        // the attachment configuration to `false` (instead of an array with steps).
        'merge-default' => false,

        // If no specific variants are set for a clipped file on a Model, these
        // variant definitions will be used.
        'default' => [

            // Fluent object format is allowed:
            // \Czim\Paperclip\Config\Steps\ResizeStep::make('variant-name')->square(50)->crop(),

            // Classic array format is allowed:
            // 'variantname' => [
            //     'strategy-alias' => [ 'strategy' => 'configuration' ],
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Preservation
    |--------------------------------------------------------------------------
    |
    | Settings to affect when attachment files are destroyed.
    |
    */

    // Set this to true in order to prevent older file uploads from being deleted.
    'keep-old-files' => false,

    // Set this to true in order to prevent file uploads from being deleted as attachments are destroyed.
    'preserve-files' => false,

    // A string value that, when set on an attachment property, will delete the attachment.
    'delete-hash' => Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT,

    /*
    |--------------------------------------------------------------------------
    | Imagine
    |--------------------------------------------------------------------------
    |
    | The default binding to use for the ImagineInterface. May be Gd or Imagick.
    |
    */

    'imagine' => Imagine\Gd\Imagine::class,

    /*
    |--------------------------------------------------------------------------
    | Processing
    |--------------------------------------------------------------------------
    |
    | Settings for (re)processing attachments.
    |
    */

    'processing' => [
        'chunk-size' => 500,
    ],

];
