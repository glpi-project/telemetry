<?php namespace GLPI\Telemetry\Models;

class Reference extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'reference';
    protected $guarded = [
      'is_displayed'
    ];
}
