<?php

namespace Zareismail\Chapar\Nova\Metrics;

use Laravel\Nova\Nova; 
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Http\Requests\NovaRequest;
use Zareismail\Chapar\Models\ChaparLetter; 

class LettersPerRecipients extends Partition
{ 
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->count($request, ChaparLetter::root(), 'recipient_type')
                    ->label(function($label) { 
                        if($resource = Nova::resourceForModel($label)) {
                            return $resource::label();
                        }

                        return $label;
                    });
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'letters-per-recipients';
    }
}
