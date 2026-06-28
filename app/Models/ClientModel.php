<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientModel extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function (ClientModel $model) {
            if ($model->isDirty('status')) {
                if (in_array($model->status, ['finished_paid', 'finished_unpaid'])) {
                    if (empty($model->completed_at)) {
                        $model->completed_at = now();
                    }
                } else {
                    $model->completed_at = null;
                }
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
