<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QrCode extends Model
{
    protected $fillable = [
        'shipment_id',
        'invoice_id',
        'qr_code_path',
        'qr_code_data',
        'generated_at'
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'qr_code_data' => 'json'
    ];

    /**
     * علاقة مع جدول الشحنات
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
