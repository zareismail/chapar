<?php

namespace Zareismail\Chapar\Models;  
     
use Illuminate\Database\Eloquent\{Model, SoftDeletes}; 

class ChaparSubject extends Model
{  
    use SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [ 
    ]; 

    /**
     * Query the related HasMany.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function letters()
    { 
        return $this->hasMany(ChaparLetter::class, 'subject_id');
    }  
}
