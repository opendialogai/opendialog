<?php

namespace App\Http\Terranet\Administrator;

use Pingpong\Menus\Menu;
use Pingpong\Menus\MenuBuilder;
use Pingpong\Menus\MenuItem;
use Terranet\Administrator\Contracts\Module\Navigable;

class Navigation
{
    protected $navigation;

    public function __construct(Menu $navigation)
    {
        $this->navigation = $navigation;
    }

    public function make()
    {
        $this->makeSidebar();

        $this->makeTools();

        return $this->navigation;
    }

    /**
     * Make SideBar navigation.
     */
    protected function makeSidebar()
    {
        $this->navigation->create(Navigable::MENU_SIDEBAR, function (MenuBuilder $sidebar) {
            $sidebar->header('Main Menu');

            // Dashboard
            $sidebar->route('scaffold.dashboard', trans('administrator::module.dashboard'), [], 1, [
                'id' => 'dashboard',
                'icon' => 'fa fa-dashboard',
                'active' => str_is(request()->route()->getName(), 'scaffold.dashboard'),
            ]);

            // Create new users group
            $sidebar->dropdown(trans('administrator::module.groups.users'), function (MenuItem $sub) {
                $sub->route('scaffold.create', trans('administrator::buttons.create_item', ['resource' => 'User']), ['module' => 'users'], 1, []);
            }, 2, ['id' => 'groups', 'icon' => 'fa fa-group']);
        });
    }

    protected function makeTools()
    {
        $this->navigation->create(Navigable::MENU_TOOLS, function (MenuBuilder $tools) {
            if (app('scaffold.config')->get('file_manager')) {
                $tools->url(
                    route('scaffold.media'),
                    trans('administrator::buttons.media'),
                    98,
                    ['icon' => 'fa fa-file-text-o']
                );
            }

            if (app('scaffold.config')->get('translations.enabled')) {
                $tools->url(
                    route('scaffold.translations.index'),
                    trans('administrator::buttons.translations'),
                    99,
                    ['icon' => 'fa fa-globe']
                );
            }

            $tools->url(
                route('scaffold.logout'),
                trans('administrator::buttons.logout'),
                100,
                ['icon' => 'fa fa-mail-forward']
            );
        });
    }
}
