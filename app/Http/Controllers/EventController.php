<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * /events                              -> fixed version (eager loading + count)
     * /events?mode=naive                   -> lazy loading, query count shown
     * /events?mode=naive&strict=1          -> lazy loading with preventLazyLoading ON (throws)
     * /events?venue=1                      -> chained scopes: upcoming() + atVenue()
     * /events?filter=arrow&value=barong    -> JSONB arrow-form filter
     * /events?filter=contains&value=barong -> JSONB containment filter
     */
    public function index(Request $request)
    {
        $mode   = $request->query('mode', 'fixed');
        $filter = $request->query('filter');
        $value  = (string) $request->query('value', 'barong');

        $query = Event::query()->orderBy('id')->limit(50);

        // Step 3: two scopes chained in one query
        if ($request->filled('venue')) {
            $query->upcoming()->atVenue((int) $request->query('venue'));
        }

        if ($filter === 'arrow') {
            $query->dressCode($value);
        } elseif ($filter === 'contains') {
            $query->detailsContain(['dress_code' => $value]);
        }

        // SQL shown on the page (before eager-load extras are added)
        $sql      = $filter ? $query->toSql() : null;
        $bindings = $filter ? $query->getBindings() : [];

        DB::enableQueryLog();

        if ($mode === 'naive') {
            // AppServiceProvider turns preventLazyLoading on for development.
            // Naive mode turns it off unless ?strict=1, so we can count first.
            Model::preventLazyLoading($request->boolean('strict'));

            $events = $query->get();

            // Each ->venue and ->participants below fires its own query (N+1).
            $rows = $events->map(fn ($e) => [
                'title'        => $e->title,
                'venue'        => $e->venue->name,
                'participants' => $e->participants->count(),
            ]);
        } else {
            // Fixed: 1 query for events, 1 for venues, 1 for the counts.
            $events = $query->with('venue')->withCount('participants')->get();

            $rows = $events->map(fn ($e) => [
                'title'        => $e->title,
                'venue'        => $e->venue->name,
                'participants' => $e->participants_count,
            ]);
        }

        $queryLog = DB::getQueryLog();

        return view('events.index', [
            'rows'       => $rows,
            'mode'       => $mode,
            'queryCount' => count($queryLog),
            'queries'    => array_slice(array_column($queryLog, 'query'), 0, 5),
            'filter'     => $filter,
            'sql'        => $sql,
            'bindings'   => $bindings,
        ]);
    }

    // /events/search?q=lantern
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $events = collect();
        $sql = null;
        $bindings = [];

        if ($q !== '') {
            $query    = Event::search($q)->with('venue')->limit(50);
            $sql      = $query->toSql();
            $bindings = $query->getBindings();
            $events   = $query->get();
        }

        return view('events.search', compact('q', 'events', 'sql', 'bindings'));
    }
}