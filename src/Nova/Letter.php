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
    public static $with = ['auth', 'subject', 'recipient'];

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

            $this->when($this->isReply() || $this->isReplyRequest($request), function() use ($request) { 
                return Text::make(__('Subject'), 'subject_id')
                            ->readonly()
                            ->resolveUsing(function() use ($request) {
                                if(is_null($this->recipient)) { 
                                    return __('Reply to :letter', [
                                        'letter' => $request->findParentResourceOrFail()->title(),
                                    ]);
                                }
                            })
                            ->fillUsing(function() {})
                            ->sortable();
            }, function() use ($request) { 
                return BelongsTo::make(__('Subject'), 'subject', Subject::class)
                            ->required()
                            ->rules('required')
                            ->showCreateRelationButton()
                            ->withoutTrashed()
                            ->sortable();
            }),

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
                        ->searchable()
                        ->inverse('letters'),

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
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return optional($this->subject)->label.':'.optional($this->auth)->email;
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
        if($viaResource = $request->viaResource()) {
            return $viaResource::newModel() instanceof Recipient;
        }

        return false;
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
            Metrics\LettersPerDay::make(),

            Metrics\LettersPerRecipients::make(),

            Metrics\SubjectsPerDay::make(),
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