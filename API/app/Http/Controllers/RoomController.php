<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function notAvailable($number)
    {
        $room = Room::where('number', $number)->first();

        return response()->json($room->notAvailable());
    }

    public function book($number, Request $request)
    {
        $room = Room::where('number', $number)->first();
        $user = auth()->user();

        $end = Carbon::parse($request->end)->addHours(2);
        $start = Carbon::parse($request->start)->addHours(2);

        $room->bookedUsers()->attach($user, [
            'start_date' => $start,
            'end_date' => $end,
        ]);

        return response()->json("ok");
    }

    public function unBook($number, Request $request)
    {
        $room = Room::where('number', $number)->first();
        $user = auth()->user();

        $end = Carbon::parse($request->end)->addHours(2);
        $start = Carbon::parse($request->start)->addHours(2);

        $room->bookedUsers()->where('user_id', $user->id)->wherePivot('start_date', $start)->wherePivot('end_date', $end)->detach();

        return response()->json("ok");
    }

    public function setUserPresent($number, $badgeId)
    {
        $room = Room::where('number', $number)->first();
        $user = User::where('badge', $badgeId)->first();

        $now = now();
        $room->bookedUsers()->wherePivot('start_date', '<', $now)->wherePivot('end_date', '>', $now)->updateExistingPivot($user->id, ['present' => true]);

        return response()->json([
            'user' => $user,
            'room' => $room,
        ]);
    }

    public function getRoomsByFloor($floor)
    {
        $rooms = Room::where('floor', $floor)->get();

        return response()->json($rooms);
    }

    public function getAllRooms()
    {
        $rooms = Room::all();

        return response()->json($rooms);
    }

    public function getRoomDetails($number)
    {
        $room = Room::where('number', $number)->first();

        return response()->json($room);
    }


}
