<?php

namespace Zareismail\Chapar\Nova; 

use Illuminate\Http\Request;
use Laravel\Nova\Nova; 
use Laravel\Nova\Fields\{ID, Text, Boolean, Trix, BelongsTo, MorphTo, MorphMany};  
use DmitryBubyakin\NovaMedialibraryField\Fields\Medialibrary;
use Zareismail\NovaContracts\Nova\User;  
use Zareismail\NovaPolicy\Nova\Role;  
use Zareismail\Chapar\Contracts\Recipient;  

class Letter extends Resource
{  
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Zareismail\Chapar\Models\ChaparLetter::class; 

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['auth', 'subject'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
    	return [
    		ID::make(), 

            BelongsTo::make(__('Subject'), 'subject', Subject::class)
                ->required()
                ->rules('required')
                ->showCreateRelationButton()
                ->withoutTrashed(),

            BelongsTo::make(__('From'), 'auth', User::class)
                ->withoutTrashed()
                ->default($request->user()->getKey())
                ->searchable()
                ->inverse('letters')
                ->readonly(),  

            MorphTo::make(__('Recipient'), 'recipient')
                ->types($recipients = static::recipients($request)->all())
                ->withoutTrashed()
                ->searchable()
                ->inverse('letters'),

            Trix::make(__('Letter Details'), 'details')
                ->required()
                ->rules('required')
                ->withFiles('public'),

            Boolean::make(__('Prevent Reply'), 'config->prevent_reply')
                ->default(false)
                ->canSee(function($request) {
                    return $request->user()->can('preventReply', static::newModel());
                }),

            Medialibrary::make(__('Attachments'), 'attachments')
                ->autouploading()
                ->hideFromIndex()
                ->nullable()
                ->fields(function () {
                    return [
                        Text::make('File Name', 'file_name')
                            ->rules('required', 'min:2'),  
                    ];
                }),

            $this->when(! $this->replyBlocked(), function() {
                return MorphMany::make(__('Replies'), 'replies', static::class);
            }),
    	];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return optional($this->subject)->label.':'.$this->auth->email;
    }

    /**
     * Return Nova's Recipient resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Nova\ResourceCollection
     */
    public static function recipients(Request $request)
    {
        return Nova::authorizedResources($request)->filter(function($resource) {
            return $resource::newModel() instanceof Recipient;
        });
    }
}