<?php

namespace App\Http\Controllers;
use App\Models\Admin;

use Illuminate\Http\Request;

class MetaDataController extends Controller
{

    public function loadMetaData(Request $request){

        $introducers = Admin::where('role' , 'introducer')->paginate(1000000000000000);

        return response()->json([
            'introducers' => $introducers
        ]);

    }

}
