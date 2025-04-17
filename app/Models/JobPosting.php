<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Requirement;
use App\Models\Responsibility;
class JobPosting extends Model
{
    use HasFactory;
    protected $fillable = ['code','status','icon','title','description','date_posted','address'];

    public function responsibilities() :HasMany{
        return $this->hasMany(Responsibility::class, 'job_posting_id');
    }

    public function requirements() :HasMany{
        return $this->hasMany(Requirement::class, 'job_posting_id');
    }
}
