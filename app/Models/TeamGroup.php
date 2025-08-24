<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamGroup extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'client_id',
    ];


    public function staff()
    {
        return $this->belongsToMany(ClientStaff::class,'team_group_user_rels','team_id','user_id');
    }
}
