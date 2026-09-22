<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_user_id',
        'shop_id',
        'product_id',
        'last_message_at',
        'is_ai_handled',
        'human_requested_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_ai_handled' => 'boolean',
        'human_requested_at' => 'datetime',
    ];

    /** The AI Assistant chat: belongs to the customer only, no vendor and no product. */
    public function isAiSupport(): bool
    {
        return $this->shop_user_id === null && $this->product_id === null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shopUser()
    {
        return $this->belongsTo(ShopUser::class);
    }

    // The conversation belongs to the shop, not to whichever single staff member started it,
    // so every staff member with chat-reply access shares the same inbox.
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }
}
