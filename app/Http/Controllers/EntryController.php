<?php

namespace App\Http\Controllers;

use App\Entry;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    public function index(Request $request)
    {
        if (request()->api_key != config('api_key.key')) {
            abort(403, "API_KEY IS INVALID");
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Turn the given location coordinate into a Geometry Point
        $locationPoint = Point::fromString(
            $request->latitude . ' ' . $request->longitude
        );

        // Get the value of the nearest Point from the given Location up to 50 meters,
        // Where the entry was updated in the last 24 hours
        $entry = Entry::distance('location', $locationPoint, 50)
            ->where('updated_at', '>', now()->subHours(24))
            ->orderByDistance('location', $locationPoint)
            ->orderBy('updated_at', 'DESC')
            ->first();

        return response()->json([
            'Latitude'          =>  $entry->location->getLat(),
            'Longitude'         =>  $entry->location->getLng(),
            'Device_ID'         =>  $entry->device_id,
            'Humidity'          =>  $entry->humidity,
            'Temperature'       =>  $entry->temperature,
            'PM1_0'             =>  $entry->pm1_0,
            'PM2_5'             =>  $entry->pm2_5,
            'PM10'              =>  $entry->pm10,
            'NoParticles_0_3'   =>  $entry->noparticles_0_3,
            'NoParticles_0_5'   =>  $entry->noparticles_0_5,
            'NoParticles_1_0'   =>  $entry->noparticles_1_0,
            'NoParticles_2_5'   =>  $entry->noparticles_2_5,
            'NoParticles_5_0'   =>  $entry->noparticles_5_0,
            'NoParticles_10'    =>  $entry->noparticles_10,
            'AQI'               =>  $entry->aqi,
            'updated_at'        =>  $entry->updated_at->toDateTimeString()
        ]);
    }

    public function store(Request $request)
    {
        if (request()->payload_fields['api_key'] != config('api_key.key')) {
            abort(403, "API_KEY IS INVALID");
        }

        error_log(print_r($request->all(), true), 3, storage_path() . '/logs/request.log');

        // Turn the given location coordinate into a Geometry Point
        $locationPoint = Point::fromString(
            $request->payload_fields['Latitude'] . ' ' . $request->payload_fields['Longitude']
        );

        // If the given Point already stored, update its value.
        // Otherwise create a new entry
        $entry = Entry::equals('location', $locationPoint)->first()
            ?
            : Entry::make(['location' => $locationPoint]);

        $entry->device_id         = $request->payload_fields['Device_ID'];
        $entry->humidity          = $request->payload_fields['Humidity'];
        $entry->temperature       = $request->payload_fields['Temperature'];
        $entry->pm1_0             = $request->payload_fields['PM1_0'];
        $entry->pm2_5             = $request->payload_fields['PM2_5'];
        $entry->pm10              = $request->payload_fields['PM10'];
        $entry->noparticles_0_3   = $request->payload_fields['NoParticles_0_3'];
        $entry->noparticles_0_5   = $request->payload_fields['NoParticles_0_5'];
        $entry->noparticles_1_0   = $request->payload_fields['NoParticles_1_0'];
        $entry->noparticles_2_5   = $request->payload_fields['NoParticles_2_5'];
        $entry->noparticles_5_0   = $request->payload_fields['NoParticles_5_0'];
        $entry->noparticles_10    = $request->payload_fields['NoParticles_10'];
        $entry->aqi               = $request->payload_fields['AQI'];

        $entry->touch();

        $saved = $entry->save();

        error_log(print_r(['saved' => $saved, 'time' => now()], true), 3, storage_path() . '/logs/saved.log');

        return response()->json(['success' => $saved], $saved ? 201 : 409);
    }
}
