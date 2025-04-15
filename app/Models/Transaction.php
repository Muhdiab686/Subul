<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'currency',
        'related_entity_id',
        'related_entity_type',
        'description',
        'reference',
        'status'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function relatedEntity()
    {
        return $this->morphTo();
    }
}
