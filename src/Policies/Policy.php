<?php

namespace Zareismail\Chapar\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Access\HandlesAuthorization; 

class Policy
{
    use HandlesAuthorization; 

    /**
     * Determine whether the user can view any resource.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return mixed
     */
    public function viewAny(Authenticatable $user)
    {
        return $user->isDeveloper();
    }

    /**
     * Determine whether the user can view the resource
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @return mixed
     */
    public function view(Authenticatable $user, Model $resource)
    {
        return $user->isDeveloper();
    }

    /**
     * Determine whether the user can create resource.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return mixed
     */
    public function create(Authenticatable $user)
    {
        return $user->isDeveloper();
    }

    /**
     * Determine whether the user can update the resource
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @return mixed
     */
    public function update(Authenticatable $user, Model $resource)
    {
        return $user->isDeveloper();
    }

    /**
     * Determine whether the user can delete the resource
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @return mixed
     */
    public function delete(Authenticatable $user, Model $resource)
    {
        return $user->isDeveloper();
    }

    /**
     * Determine whether the user can restore the resource
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @return mixed
     */
    public function restore(Authenticatable $user, Model $resource)
    {
        return $user->isDeveloper();
    }

    /**
     * Determine whether the user can permanently delete the resource
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @return mixed
     */
    public function forceDelete(Authenticatable $user, Model $resource)
    {
        return $user->isDeveloper();
    } 
}
