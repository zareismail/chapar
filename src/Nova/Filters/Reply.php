<?php

namespace Zareismail\Chapar\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class Reply extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query
                ->when($value == 'reply', function($query) {
                    $query->reply();
                })
                ->when($value == 'not_reply', function($query) {
                    $query->root();
                })
                ->when($value == 'replied', function($query) {
                    $query->whereHas('replies');
                })
                ->when($value == 'not_replied', function($query) {
                    $query->doesntHave('replies');
                });
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            __('Is Reply') => 'reply',
            __('Is Not Reply') => 'not_reply',
            __('Is Replied') => 'replied',
            __('Is Not Replied') => 'not_replied',
        ];
    }
}
