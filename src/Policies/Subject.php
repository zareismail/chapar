<?php 

namespace Zareismail\Chapar\Policies;


class Subject extends Policy
{   
    /**
     * Determine whether the user can create resource.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return mixed
     */
    public function create($user)
    {
        return call_user_func_array([Letter::class, 'create'], func_get_args());
    }
}