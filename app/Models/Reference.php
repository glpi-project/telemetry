<?php namespace App\Models;

class Reference extends \Illuminate\Database\Eloquent\Model {
   protected $table = 'reference';
   protected $guarded = [
      'is_displayed'
   ];
}