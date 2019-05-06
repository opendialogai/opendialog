<?php

namespace App\RevisionViewer;

use Laravel\Nova\ResourceTool;

class RevisionViewer extends ResourceTool
{
    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return 'Revisions';
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'RevisionViewer';
    }
}
