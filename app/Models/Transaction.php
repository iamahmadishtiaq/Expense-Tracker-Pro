<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'type',
        'amount',
        'transaction_date',
        'description',
        'to_account_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }
}
