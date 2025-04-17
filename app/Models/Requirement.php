<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\JobPosting;

class Requirement extends Model
{
    use HasFactory;
    protected $fillable = ['job_posting_id','requirement'];

    public function job() :BelongsTo{
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }
}
