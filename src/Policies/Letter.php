<?php 

namespace Zareismail\Chapar\Policies;


class Letter extends Policy
{ 
	public function preventReply($user)
	{
        return $user->isDeveloper();
	}
}