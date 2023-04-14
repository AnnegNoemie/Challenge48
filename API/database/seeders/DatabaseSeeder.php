<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Carbon\Carbon;
use Faker\Factory;
use App\Models\Room;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Factory::create();
        // \App\Models\User::factory(10)->create();
        for ($i=0; $i < 10; $i++) {
            $group = Group::create([
                'name' => 'classe ' . ($i + 1),
            ]);
            for ($j=0; $j < 25; $j++) {
                User::create([
                    'first_name' => $faker->firstName,
                    'last_name' => $faker->lastName,
                    'email' => $faker->email,
                    'password' => bcrypt('password'),
                    'badge' => Str::uuid(),
                    'group_id' => $group->id,
                ]);
            }
        }

        $user = User::create([
            'first_name' => 'romain',
            'last_name' => 'silvy',
            'email' => 'romain@silvy-leligois.fr',
            'password' => bcrypt('password'),
            'badge' => Str::uuid(),
            'group_id' => '1'
        ]);

        for ($i = 0; $i <= 4; $i++) {
            for ($j = 0; $j < 4; $j++) {
                Room::create([
                    'name' => 'Salle ' . ($i) . '0' . ($j + 1),
                    'number' => ($i) . '0' . ($j + 1),
                    'floor' => $i,
                    'capacity' => 25,
                ]);
            }
        }

        // attach users to rooms
        $rooms = Room::all();
        $firstRoom = $rooms->first();
        $rooms = $rooms->skip(1);
        $users = User::all();

        $randUsers = $users->except([251])->random(25);
        foreach ($randUsers as $user) {
            $start_date = '2023-04-14 08:00:00';
            $end_date = '2023-04-14 19:00:00';



            $user->rooms()->attach($firstRoom, [
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);
        }



        foreach ($rooms as $room) {
            // Sélectionner un nombre aléatoire d'utilisateurs (jusqu'à 10) pour chaque chambre
            $num_users = $faker->numberBetween(1, 25);
            $selected_users = $users->random($num_users);
            // add the user 251 to each room
            $selected_users->add(User::find(251));


            // Générer des réservations aléatoires pour chaque utilisateur sélectionné
            foreach ($selected_users as $user) {
                // Définir une plage horaire entre 8h et 17h pour chaque jour
                $start_time = Carbon::parse('8:00');
                $end_time = Carbon::parse('17:00');

                // Générer des dates aléatoires dans cette plage horaire pour les heures de départ et de fin
                $start_date = $faker->dateTimeBetween('-1 week', '+1 week');
                $start_date->setTime($start_time->hour + $faker->numberBetween(0, $end_time->hour - $start_time->hour), 0, 0);
                $end_date = Carbon::parse($start_date)->addHours(rand(1, 4));
                $end_date->setTime($end_date->startOfHour()->hour, 0, 0);

                $user->rooms()->attach($room, [
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ]);


                $start_date = $faker->dateTimeBetween('-1 week', '+1 week');
                $start_date->setTime($start_time->hour + $faker->numberBetween(0, $end_time->hour - $start_time->hour), 0, 0);
                $end_date = Carbon::parse($start_date)->addHours(rand(1, 4));
                $end_date->setTime($end_date->startOfHour()->hour, 0, 0);

                $user->rooms()->attach($room, [
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ]);

                $start_date = $faker->dateTimeBetween('-1 week', '+1 week');
                $start_date->setTime($start_time->hour + $faker->numberBetween(0, $end_time->hour - $start_time->hour), 0, 0);
                $end_date = Carbon::parse($start_date)->addHours(rand(1, 4));
                $end_date->setTime($end_date->startOfHour()->hour, 0, 0);

                $user->rooms()->attach($room, [
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ]);
            }
        }
    }
}
