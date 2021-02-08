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
        $locationPoint = new Point($request->latitude, $request->longitude);

        // Get the value of the nearest Point from the given Location up to 50 meters,
        // Where the entry was updated in the last 24 hours
        $entry = Entry::distanceSphere('location', $locationPoint, 50)
            ->where('updated_at', '>', now()->subHours(24))
            ->orderByDistanceSphere('location', $locationPoint)
            ->orderBy('updated_at', 'DESC')
            ->first();

        if ($entry) {
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
                'metadata'          =>  json_decode($entry->metadata, true),
                'otherdata'         =>  json_decode($entry->otherdata, true),
                'updated_at'        =>  $entry->updated_at->toDateTimeString()
            ]);
        } else {
            return response()->json([
                'Latitude'          =>  "",
                'Longitude'         =>  "",
                'Device_ID'         =>  "",
                'Humidity'          =>  0,
                'Temperature'       =>  0,
                'PM1_0'             =>  0,
                'PM2_5'             =>  0,
                'PM10'              =>  0,
                'NoParticles_0_3'   =>  0,
                'NoParticles_0_5'   =>  0,
                'NoParticles_1_0'   =>  0,
                'NoParticles_2_5'   =>  0,
                'NoParticles_5_0'   =>  0,
                'NoParticles_10'    =>  0,
                'AQI'               =>  0,
                'metadata'          =>  "",
                'otherdata'         =>  "",
                'updated_at'        =>  ""
            ]);
        }
    }

    public function store(Request $request)
    {
        if (request()->payload_fields['api_key'] != config('api_key.key')) {
            abort(403, "API_KEY IS INVALID");
        }

        $request->validate([
            'payload_fields' => 'required|array',
            'metadata' => 'required|array',
        ]);

        // Turn the given location coordinate into a Geometry Point
        $locationPoint = new Point(
            $request->payload_fields['Latitude'],
            $request->payload_fields['Longitude']
        );

        // If the given Point already stored, update its value.
        // Otherwise create a new entry
        $entry = Entry::make(['location' => $locationPoint]);

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
        $entry->metadata          = json_encode($request->metadata);
        $entry->otherdata         = json_encode([
            $request->app_id,
            $request->dev_id,
            $request->hardware_serial,
            $request->port,
            $request->counter,
            $request->confirmed,
            $request->is_retry,
            $request->payload_raw,
            $request->downlink_url,
        ]);

        $saved = $entry->save();

        return response()->json(['success' => $saved], $saved ? 201 : 409);
    }
}
