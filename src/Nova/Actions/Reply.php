<?php

namespace Zareismail\Chapar\Nova\Actions;
 
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Zareismail\Chapar\Nova\Letter;

class Reply extends Action
{  
    /**
     * Disables action log events for this action.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

    /**
     * Determine where the action redirection should be without confirmation.
     *
     * @var bool
     */
    public $withoutConfirmation = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $uri = Letter::uriKey();

        return Action::push("/resources/{$uri}/new", [
          'viaResource' => $uri,
          'viaResourceId' => $models->first()->getKey(),
          'viaRelationship' => 'replies'
        ]);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
