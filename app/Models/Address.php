<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $table = 'addresses'; // Pastikan nama tabel sesuai
    protected $fillable = ['contact_id', 'street', 'city', 'province', 'country', 'postal_code'];

    public $timestamps = false; // Nonaktifkan timestamps jika tidak digunakan

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }
}
