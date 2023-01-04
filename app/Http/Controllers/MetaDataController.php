<?php

namespace App\Http\Controllers;
use App\Models\Introducer;

use Illuminate\Http\Request;

class MetaDataController extends Controller
{

    public function loadMetaData(Request $request){

        $introducers = Introducer::all();

        return response()->json([
            'introducers' => $introducers
        ]);

    }

}
