<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Wishlist extends Model
{
    use HasFactory;
    use HasUuids;

    // Guarded attributtes
    protected $guarded = ['id'];

    /**
     * Fetch the users
     * that this wishlist belongs
     * to
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role');
    }

    /**
     * Get the owner of this wishlist
     */
    public function owner()
    {
        return $this->belongsToMany(User::class)->wherePivot('role', 'owner')->firstOrFail();
    }

    /**
     * Get the items for this wishlist
     */
    public function items()
    {
        return $this->hasMany(WishlistItem::class);
    }

    /**
     * Is this wishlist public or not
     */
    public function isPublic()
    {
        return $this->public;
    }

    public function getUnpurchasedCount()
    {
        return $this->items()->sum('needs') - $this->items()->sum('has');
    }
}
