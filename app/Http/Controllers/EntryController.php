<?php

namespace App\Http\Controllers;

use App\Entry;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    public function index(Request $request)
    {
        // Validate sent data are in correct format
        $request->validate([
            'location'  =>  ['required', 'regex:/^(-?\d+(\.\d+)?),*(-?\d+(\.\d+)?)$/'],
        ]);

        // Turn the given location coordinate into a Geometry Point
        $locationPoint = Point::fromString(
            str_replace(',', ' ', $request->location)
        );

        // Get the value of the nearest Point from the given Location up to 20 meters,
        // Where the entry was updated in the last 10 minutes
        $entry = Entry::distance('location', $locationPoint, 50)
            ->where('updated_at', '>', now()->subHours(24))
            ->orderByDistance('location', $locationPoint)
            ->orderBy('updated_at', 'DESC')
            ->first();

        return response()->json($entry);
    }

    public function store(Request $request)
    {
        // Validate sent data are in correct format
        // $request->validate([
        //     'location'  =>  ['required', 'regex:/^(-?\d+(\.\d+)?),*(-?\d+(\.\d+)?)$/'],
        //     'pollution_rate' => ['required', 'numeric']
        // ]);

        error_log(print_r($request->all(), true), 3, storage_path() . '/logs/request.log');

        // Turn the given location coordinate into a Geometry Point
        $locationPoint = Point::fromString(
            str_replace(',', ' ', $request->payload['location'])
        );

        // If the given Point already stored, update its value.
        // Otherwise create a new entry
        $entry = Entry::equals('location', $locationPoint)->first()
            ?
            : Entry::make(['location' => $locationPoint]);

        $entry->pollution_rate = $request->payload['pollution_rate'];

        $entry->updated_at = now();
        $saved = $entry->save();

        error_log(print_r(['saved' => $saved, 'time' => now()], true), 3, storage_path() . '/logs/saved.log');

        return response()->json($entry);
    }
}
