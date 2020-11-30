<?php

namespace Zareismail\Hafiz\Concerns; 

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Zareismail\Chapar\Models\ChaparLetter;

trait InteractsWithLetters
{ 
	/**
	 * Query the related Environmentals.
	 * 
	 * @return \Illuminate\Database\Eloquent\Relations\HasOneOrMany
	 */
	public function letters(): HasOneOrMany
	{
		return $this->morphMany(ChaparLetter::class, 'recipient');
	}
} 