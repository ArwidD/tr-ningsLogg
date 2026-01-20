<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class WorkoutController extends BaseController
{
    public function index()
    {
        $workouts = DB::table('workouts')->orderBy('date', 'desc')->get();
        return response()->json($workouts);
    }

    public function store(Request $request)
    {
        $data = $request->only(['date', 'activity', 'difficulty', 'distance', 'duration']);
        $id = DB::table('workouts')->insertGetId($data);
        return response()->json([
            'success' => true,
            'id' => $id,
            'message' => 'Workout added'
        ]);
    }
}
