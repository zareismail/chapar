<?php

namespace Zareismail\Chapar\Nova; 

use Illuminate\Http\Request;
use Laravel\Nova\Nova; 
use Laravel\Nova\Fields\{ID, Text, Boolean, Trix, BelongsTo, MorphMany};  
use DmitryBubyakin\NovaMedialibraryField\Fields\Medialibrary;
use Zareismail\NovaContracts\Nova\User;  
use Zareismail\NovaPolicy\Nova\Role;  
use Zareismail\Fields\MorphTo;  
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
    public static $with = ['auth', 'recipient'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    { 
    	return [
    		ID::make()->sortable(),  

            BelongsTo::make(__('From'), 'auth', User::class)
                ->withoutTrashed()
                ->default($request->user()->getKey())
                ->searchable()
                ->inverse('letters')
                ->readonly()
                ->sortable(),   

            MorphTo::make(__('Recipient'), 'recipient')
                        ->types($recipients = static::recipients($request)->all())
                        ->withoutTrashed() 
                        ->inverse('letters'),

            Text::make(__('Subject'), 'subject') 
                ->sortable()
                ->required()
                ->rules('required')
                ->readonly($this->isReplyRequest($request) && $request->isMethod('get'))
                ->withMeta(array_filter([
                    'value' => $this->isReplyRequest($request) ? __('Replied to: :subject', [
                        'subject' => $request->findParentResourceOrFail()->title()
                    ]) : null
                ])),

            Trix::make(__('Letter Details'), 'details')
                ->required()
                ->rules('required')
                ->withFiles('public'),

            Boolean::make(__('Prevent Reply'), 'config->prevent_reply')
                ->default(false)
                ->sortable()
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

    public function isReplyRequest(Request $request)
    {  
        return $request->viaResource() === static::class;
    }

    /**
     * Get the cards available on the entity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [ 
            Metrics\LettersPerDay::make()->width('1/2'),

            Metrics\LettersPerRecipients::make()->width('1/2'), 
        ];
    }

    /**
     * Get the actions available on the entity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            Actions\Reply::make()
                ->onlyOnTableRow()
                ->canSee(function ($request) { 
                    return ! optional($this->resource)->replyBlocked();
                }),
        ];
    }
}