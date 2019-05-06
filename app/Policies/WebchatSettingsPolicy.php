<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use OpenDialogAi\Webchat\WebchatSetting;

class WebchatSettingsPolicy
{
    use HandlesAuthorization;

    public function update(User $user, WebchatSetting $webchatSetting)
    {
        return $webchatSetting->children()->count() == 0;
    }

    public function create(User $user)
    {
        return false;
    }

    public function delete(User $user)
    {
        return false;
    }

    public function view(User $user, WebchatSetting $webchatSetting)
    {
        return $webchatSetting->children()->count() > 0;
    }
}
