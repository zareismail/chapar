<?php

namespace Zareismail\Chapar\Nova; 

use Illuminate\Http\Request;
use Laravel\Nova\Nova; 
use Laravel\Nova\Http\Requests\NovaRequest;
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

            Trix::make(__('Letter Details'), 'message')
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
                })
                ->canRun(function ($request) { 
                    return $request->user()->can('create', static::newModel());
                }),
        ];
    } 

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return parent::indexQuery($request, $query)
                    ->when(static::shouldAuthenticate($request), function($query) use ($request) {
                        $morphs = static::recipients($request)->map(function($recipient) {
                            return \Zareismail\NovaPolicy\Helper::isOwnable($recipient::$model) 
                                        ? $recipient::$model : null;
                        })->filter();

                        $query->orWhere(function($query) use ($request) {
                            $query->where('recipient_type', User::newModel()->getMorphClass())
                                  ->where('recipient_id', $request->user()->id);
                        })
                        ->orWhereHasMorph('recipient', $morphs->all(), function($query, $type) {
                            $query->authenticate();
                        });
                    })
                    ->when($request->viaResource() === static::class, function($query) use ($request) { 
                        $query->whereHas('repliedTo', function($query) use ($request) {
                            $query->whereKey($request->viaResourceId);
                        });
                    });
    }

    /**
     * Determine if the current user can view the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ability
     * @return bool
     */
    public function authorizedTo(Request $request, $ability)
    {
        return parent::authorizedTo($request, $ability) || 
            $ability === 'view' && (
                $request->user()->can('view', $this->recipient) || $this->recipient->is($request->user())
            );
    }
}
