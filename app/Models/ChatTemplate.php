<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatTemplate extends Model
{
    protected $fillable = ['keyword', 'answer'];
}
