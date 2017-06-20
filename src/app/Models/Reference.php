<?php namespace App\Models;

class Reference extends \Illuminate\Database\Eloquent\Model {
   protected $table = 'reference';
   /*protected $guarded = [
      'id', 'date_creation', 'is_displayed'
   ];*/

   protected $fillable = [
   "name", "url", "country", "phone", "email", "referent", "num_assets", "num_helpdesk", "comment"
   ];
}