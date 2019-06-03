<?php

namespace App\Http\Terranet\Administrator\Finders;

use Terranet\Administrator\Services\Finder;

class WebchatSettings extends Finder
{
    /**
     * Fetch all items from repository
     *
     * @return mixed
     */
    public function fetchAll()
    {
        return $this->getQuery()->whereNull('parent_id')->paginate($this->perPage());
    }

    /**
     * Find a record by id
     *
     * @param       $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $this->model = $this->model->newQueryWithoutScopes()->findOrFail($id, $columns);

        return $this->model;
    }
}
