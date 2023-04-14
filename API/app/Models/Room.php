<?php

namespace App\Models;

use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
    ];

    protected $appends = [
        'presents',
        'booked',
        'not_presents',
        'status',
    ];

    public function getPresentsAttribute()
    {
        $rooms = $this->bookedUsers()
            ->wherePivot('present', true)
            ->wherePivot('start_date', '<=', now())
            ->wherePivot('end_date', '>=', now())
            ->get()->count();

        return $rooms;
    }

    public function getNotPresentsAttribute()
    {
        return $this->booked - $this->presents;
    }

    public function getStatusAttribute()
    {
        $rooms = $this->bookedGroups()
            ->wherePivot('start_date', '<=', now())
            ->wherePivot('end_date', '>=', now())
            ->get()->count();
        // get the numer of booked
        if ($rooms > 0) {
            return 'Indisponible';
        } else if ($this->booked >= $this->capacity) {
            return 'Complet';
        } else {
            return 'Libre';
        }

    }
    public function getBookedAttribute()
    {
        $rooms = $this->bookedUsers()
            ->wherePivot('present', false)
            ->wherePivot('start_date', '<=', now())
            ->wherePivot('end_date', '>=', now())
            ->get()->count();

        return $rooms;
    }

    public function bookedUsers()
    {
        return $this->morphedByMany(User::class, 'bookable')->withPivot(['start_date', 'end_date', 'present']);
    }

    public function bookedGroups()
    {
        return $this->morphedByMany(Group::class, 'bookable')->withPivot(['start_date', 'end_date', 'present']);
    }


    public function notAvailable()
    {
        $notAvailable = [];

        foreach ($this->bookedGroups()->get() as $group) {
            $start = $group->pivot->start_date;
            $end = $group->pivot->end_date;

            $notAvailable[] = [
                'title' => 'Cours',
                'start' => $start,
                'end' => $end,
            ];
        }

        foreach ($this->bookedUsers()->get() as $user) {
            $start = $user->pivot->start_date;
            $end = $user->pivot->end_date;

            $numUsers = $this->bookedUsers()
                ->wherePivot('start_date', $start)
                ->wherePivot('end_date', $end)
                ->count();


            if ($user->id == auth()->user()->id) {
                $notAvailable[] = [
                    'title' => 'Reservé',
                    'start' => $start,
                    'end' => $end,
                ];

            } else if ($numUsers >= $this->capacity) {
                $newEntry = [
                    'title' => 'Complet',
                    'start' => $start,
                    'end' => $end,
                ];

                $entryExists = array_reduce($notAvailable, function ($carry, $entry) use ($newEntry) {
                    return $carry || ($entry['start'] == $newEntry['start'] && $entry['end'] == $newEntry['end']);
                }, false);

                if (!$entryExists) {
                    $notAvailable[] = $newEntry;
                }
            }
        }

        return $notAvailable;
    }
}
