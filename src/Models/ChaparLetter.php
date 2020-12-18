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
	public function subject()
	{ 
		return $this->belongsTo(ChaparSubject::class);
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

    /**
     * Query where that is a reply.
     * 
     * @param  \Illuminate\Database\Eloquent\Builder $query 
     * @return \Illuminate\Database\Eloquent\Builder        
     */
    public function scopeReply($query)
    {
        return $query->typeOf(static::class)->haveRecipient();
    }

    /**
     * Query where that is not a reply.
     * 
     * @param  \Illuminate\Database\Eloquent\Builder $query 
     * @return \Illuminate\Database\Eloquent\Builder        
     */
    public function scopeRoot($query)
    {
        return $query->notTypeOf(static::class);
    } 

    /**
     * Query where doesn`t have recipiant
     * 
     * @param  \Illuminate\Database\Eloquent\Builder $query 
     * @return \Illuminate\Database\Eloquent\Builder   
     */
    public function scopeDoesntHaveRecipient($query)
    {
        return $query->whereNull($this->getQualifiedRecipientIdColumn());
    } 

    /**
     * Query where have recipiant
     * 
     * @param  \Illuminate\Database\Eloquent\Builder $query 
     * @return \Illuminate\Database\Eloquent\Builder   
     */
    public function scopeHaveRecipient($query)
    {
        return $query->whereNotNull($query->getQualifiedRecipientIdColumn());
    }

    /**
     * Return qulified name of recipiant_type column.
     *  
     * @return string
     */
    public function getQualifiedRecipientTypeColumn()
    {
        return $this->qualifyColumn('recipient_type');
    } 

    /**
     * Return qulified name of recipiant_id column.
     *  
     * @return string
     */
    public function getQualifiedRecipientIdColumn()
    {
        return $this->qualifyColumn('recipient_id');
    }

    /**
     * Query where that is not a reply.
     * 
     * @param  \Illuminate\Database\Eloquent\Builder $query 
     * @param  string $recipient
     * @return \Illuminate\Database\Eloquent\Builder        
     */
    public function scopeTypeOf($query, $recipient)
    {
        return $query->whereRecipientType($recipient);
    }

    /**
     * Query where that is not a reply.
     * 
     * @param  \Illuminate\Database\Eloquent\Builder $query 
     * @param  string $recipient
     * @return \Illuminate\Database\Eloquent\Builder        
     */
    public function scopeNotTypeOf($query, $recipient)
    {
        return $query->where($this->getQualifiedRecipientTypeColumn(), '!=', $recipient);
    }

    public function registerMediaCollections(): void
    { 
        $this->addMediaCollection('attachments');
    }
}
