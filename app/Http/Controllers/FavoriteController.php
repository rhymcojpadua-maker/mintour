<?php
namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Destination $destination): JsonResponse
    {
        $existing = Favorite::where('user_id', $request->user()->id)
            ->where('destination_id', $destination->id)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['favorited' => false]);
        }

        Favorite::create([
            'user_id'        => $request->user()->id,
            'destination_id' => $destination->id,
        ]);

        return response()->json(['favorited' => true]);
    }
}
