<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $wishlist = $this->wishlist();

        return [
            'id' => $this->id,
            'wishlist_id' => $wishlist->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'price' => $this->price,
            'url' => $this->url,
            'comment' => $this->comment,
            'image' => $this->image,
            'needs' => $this->needs,
            'has' => $this->has,
            'created_at' => $this->created_at,
            'hasCurrentUserReservation' => $this->when($request->user(), function () use ($request) {
                return $this->hasUserReservation($request->user());
            }),
            'can' => $this->when($request->user(), function () use ($wishlist, $request) {
                return [
                    'update' => $request->user()->can('update', $wishlist),
                    'delete' => $request->user()->can('delete', $wishlist),
                    'mark' => $request->user()->can('markAsPurchased', $wishlist),
                ];
            }),
        ];
    }
}
