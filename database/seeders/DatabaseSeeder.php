<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    private const TOTAL_EVENTS  = 20000;
    private const CHUNK_SIZE    = 1000;
    private const PARTICIPANTS  = 200;

    public function run(): void
    {
        $now = now();

        // ---- Venues (a few more than Week 10 so the data is spread out)
        $venueIds = collect([
            ['Main Hall', 'Los Baños'], ['Science Auditorium', 'Calamba'],
            ['Open Grounds', 'Santa Rosa'], ['Gymnasium', 'Calauan'],
            ['Library Atrium', 'Nagcarlan'],
        ])->map(fn ($v) => Venue::create(['name' => $v[0], 'city' => $v[1]])->id)->all();

        // ---- Building blocks for searchable descriptions
        $openers = ['A hands-on session about', 'An open forum on', 'A student-led talk covering',
                    'A beginner-friendly workshop on', 'A networking afternoon focused on'];
        $topics  = ['web development', 'campus sustainability', 'career planning', 'database design',
                    'mobile apps', 'community outreach', 'cybersecurity basics', 'sports and wellness',
                    'creative writing', 'startup ideas'];
        $closers = ['Snacks will be provided.', 'Bring your own laptop.', 'Open to all year levels.',
                    'Certificates will be given.', 'Limited slots available.', 'Free admission.'];
        $types   = ['Workshop', 'Seminar', 'Hackathon', 'Fair', 'Forum', 'Fun Run', 'Exhibit'];

        // Common values for details
        $dressCodes = ['casual', 'smart casual', 'formal', 'school uniform', 'costume'];
        $allTags    = ['tech', 'music', 'sports', 'career', 'art', 'science', 'community'];

        // ---- Events: 20,000 rows, inserted 1,000 at a time
        for ($start = 0; $start < self::TOTAL_EVENTS; $start += self::CHUNK_SIZE) {
            $rows = [];

            for ($i = $start; $i < $start + self::CHUNK_SIZE; $i++) {
                $description = $openers[array_rand($openers)] . ' ' . $topics[array_rand($topics)]
                             . '. ' . $closers[array_rand($closers)];

                // RARE WORD: 'lantern' appears in 1 of every 250 events (0.4%)
                if ($i % 250 === 0) {
                    $description .= ' Ends with a lantern parade.';
                }

                // RARE VALUE: dress_code = 'barong' in 1 of every 200 events (0.5%)
                $dress = ($i % 200 === 7) ? 'barong' : $dressCodes[array_rand($dressCodes)];

                $details = [
                    'dress_code' => $dress,
                    'capacity'   => random_int(30, 500),
                    'tags'       => (array) array_rand(array_flip($allTags), random_int(1, 3)),
                ];

                $rows[] = [
                    'venue_id'    => $venueIds[array_rand($venueIds)],
                    'title'       => $types[array_rand($types)] . ' #' . ($i + 1),
                    'description' => $description,
                    'details'     => json_encode($details),
                    'starts_at'   => $now->copy()->addDays(random_int(-180, 180))->addMinutes(random_int(0, 1439)),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            DB::table('events')->insert($rows);
        }

        // ---- Participants: 200
        $participants = [];
        for ($p = 1; $p <= self::PARTICIPANTS; $p++) {
            $participants[] = [
                'name' => "Participant $p", 'email' => "participant{$p}@example.com",
                'created_at' => $now, 'updated_at' => $now,
            ];
        }
        DB::table('participants')->insert($participants);

        // ---- Registrations for the first 100 events (5-15 people each)
        $eventIds       = DB::table('events')->orderBy('id')->limit(100)->pluck('id');
        $participantIds = DB::table('participants')->pluck('id');
        $registrations  = [];

        foreach ($eventIds as $eventId) {
            foreach ($participantIds->shuffle()->take(random_int(5, 15)) as $participantId) {
                $registrations[] = [
                    'event_id' => $eventId, 'participant_id' => $participantId,
                    'status'   => random_int(1, 5) === 1 ? 'waitlisted' : 'registered',
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($registrations, 500) as $chunk) {
            DB::table('event_participant')->insert($chunk);
        }

        // ---- A few notes so the Week 10 /report page still works
        Event::find(1)->notes()->create(['body' => 'Bring your student ID.']);
        Event::find(2)->notes()->create(['body' => 'Teams of four only.']);
        Venue::find($venueIds[0])->notes()->create(['body' => 'Aircon is broken.']);

        // Fresh statistics so the planner makes honest choices in EXPLAIN
        DB::statement('ANALYZE events');
    }
}
