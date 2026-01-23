<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkoutController extends BaseController
{
    public function index(Request $request)
    {
        $query = DB::table('workouts');
        
        // Filtrera på startdatum om det finns
        if ($request->has('from')) {
            $query->where('date', '>=', $request->input('from'));
        }
        
        // Filtrera på slutdatum om det finns
        if ($request->has('to')) {
            $query->where('date', '<=', $request->input('to'));
        }
        
        $workouts = $query->orderBy('date', 'desc')->get();
    return response()->json($workouts);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->only(['date', 'activity', 'difficulty', 'distance', 'duration']);
            
            $id = DB::table('workouts')->insertGetId([
                'date' => $data['date'],
                'activity' => $data['activity'],
                'difficulty' => (int) $data['difficulty'],
                'distance' => $data['distance'] ?? null,
                'duration' => $data['duration'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $workout = DB::table('workouts')->where('id', $id)->first();
            return response()->json($workout, 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = DB::table('workouts')->where('id', $id)->delete();
            
            if ($deleted) {
                return response()->json(['message' => 'Workout borttaget'], 200);
            }
            
            return response()->json(['error' => 'Workout hittades inte'], 404);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}