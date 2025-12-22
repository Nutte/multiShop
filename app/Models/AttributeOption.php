<?php
// FILE: app/Models/AttributeOption.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model
{
    protected $fillable = ['type', 'value', 'slug'];
}