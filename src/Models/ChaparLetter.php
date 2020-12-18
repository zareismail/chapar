<?php

namespace Zareismail\Chapar\Models;  
     
use Illuminate\Database\Eloquent\SoftDeletes;
use Zareismail\NovaContracts\Models\AuthorizableModel;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Zareismail\Contracts\Concerns\InteractsWithConfigs;
use Zareismail\Chapar\Contracts\Recipient;

class ChaparLetter extends AuthorizableModel implements HasMedia, Recipient
{  
    use HasMediaTrait, SoftDeletes, InteractsWithConfigs;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [ 
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot(); 
    } 

	/**
	 * Query the related Recipient.
	 * 
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function recipient()
	{ 
		return $this->morphTo();
	} 

    /**
     * Query the related HasMany.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function repliedTo()
    { 
        return $this->belongsTo(static::class, 'recipient_id');
    } 

    /**
     * Query the related HasMany.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    { 
        return $this->hasMany(static::class, 'recipient_id');
    } 

    /**
     * Query the related Letters.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function letters()
    { 
        return $this->replies();
    } 

    /**
     * Determine if prevented the reply.
     * 
     * @return bool
     */
    public function replyBlocked()
    {
        return boolval($this->getConfig('prevent_reply'));
    }

    public function registerMediaCollections(): void
    { 
        $this->addMediaCollection('attachments');
    }
}
