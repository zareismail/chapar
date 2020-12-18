<?php

namespace Zareismail\Chapar\Nova; 

use Illuminate\Http\Request;
use Laravel\Nova\Nova; 
use Laravel\Nova\Fields\{ID, Text, HasMany};   

class Subject extends Resource
{  
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Zareismail\Chapar\Models\ChaparSubject::class; 

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = [];

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

    		Text::make(__('Label'), 'label')
    			->required()
    			->rules('required'), 

            HasMany::make(__('Letters'), 'letters', Letter::class),
    	];
    } 
}