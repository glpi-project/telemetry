<?php namespace GLPI\Telemetry\Models;

class Reference extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'reference';
    protected $guarded = [
      'is_displayed'
    ];
    /**
     * Scope a query to only include references that can be displayed.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_displayed', '=', true);
    }
}
