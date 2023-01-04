<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Introducer;

class IntroducerController extends Controller
{

    public function addIntroducer(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'name' => 'required|string|max:25',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $introducer = Introducer::create([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'New Introducer added successfully',
            'introducer' => $introducer
        ],200);

    }

}
