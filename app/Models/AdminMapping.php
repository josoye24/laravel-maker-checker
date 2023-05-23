<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid as Uuid;
use App\Models\User;



class AdminMapping extends Model
{
    use HasFactory;

    protected $table = 'admin_user_mapping';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $casts = [
        'id' => 'string',
    ];

    public $incrementing = false;

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'admin_id',
        'mapped_to',
        'initiated_by',
        'status',
        'approved_by',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
