<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateOrnament extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'file',
        'label',
        'position',
        'default_active',
    ];

    protected $casts = [
        'default_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
