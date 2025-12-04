<?php

namespace App\Policies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\User;

class AbstractPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $policy = Str::replace('Policy', '', class_basename(static::class));

        return $user->hasPermissionTo($policy . "." . 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $policy = Str::replace('Policy', '', class_basename(static::class));

        return $user->hasPermissionTo($policy . "." . 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        $policy = Str::replace('Policy', '', class_basename(static::class));

        if ($policy === 'User' && !$user->hasPermissionTo($policy . "." . 'update')) {
            return $user->id === $model->id;
        } else {
            return $user->hasPermissionTo($policy . "." . 'update');
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Model $model): bool
    {
        $policy = Str::replace('Policy', '', class_basename(static::class));

        if ($policy === 'User' && $user->hasPermissionTo($policy . "." . 'delete')) {
            if ($user->hasRole('Super Admin')) {
                return false;
            } else {
                return $user->id !== $model->id;
            }
        } else {
            return $user->hasPermissionTo($policy . "." . 'delete');
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Model $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }
}
