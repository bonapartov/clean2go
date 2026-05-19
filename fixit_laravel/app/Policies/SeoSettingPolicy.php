<?php

namespace App\Policies;

use App\Models\SeoSetting;
use App\Models\User;

class SeoSettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.seo_setting.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SeoSetting $SeoSetting)
    {
        if ($user->can('backend.seo_setting.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('backend.seo_setting.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SeoSetting $SeoSetting)
    {
        if ($user->can('backend.seo_setting.edit')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SeoSetting $SeoSetting)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SeoSetting $SeoSetting)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SeoSetting $SeoSetting)
    {
        //
    }
}
