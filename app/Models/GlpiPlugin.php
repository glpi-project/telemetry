<?php namespace GLPI\Telemetry\Models;

class GlpiPlugin extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'glpi_plugin';
    protected $guarded = [
      'id'
    ];
}
