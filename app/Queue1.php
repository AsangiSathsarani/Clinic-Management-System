<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Queue1 extends Model
{
    protected $table = "queue1";
    protected $fillable = ['number', 'patient_id'];
}
