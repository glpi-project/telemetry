<?php namespace GLPI\Telemetry\Models;

class Telemetry extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'telemetry';
    protected $guarded = [
      'id'
    ];
}
