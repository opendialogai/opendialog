<?php

namespace App\Http\Terranet\Administrator\Dashboard;

use Terranet\Administrator\Dashboard\DashboardRow;
use Terranet\Administrator\Dashboard\Factory as CoreFactory;
use Terranet\Administrator\Dashboard\Manager;

class Factory extends CoreFactory
{
    /**
     * Register dashboard panels.
     *
     * @return Manager
     */
    protected function registerPanels()
    {
        $this->dashboard
            ->row(function (DashboardRow $row) {
                $row->panel(new BlankPanel())->setWidth(12);
            })
//            ->row(function (DashboardRow $row) {
//                $row->panel(new GoogleAnalyticsPanel)->setWidth(12);
//            })
            ->row(function (DashboardRow $row) {
                $row->panel(new MembersPanel())->setWidth(6);
                $row->panel(new DatabasePanel())->setWidth(6);
            });

        return $this->dashboard;
    }
}
