<?php

namespace App\Http\Controllers;
use App\Models\Admin;
use App\Models\BugReport;

use Illuminate\Http\Request;

class MetaDataController extends Controller
{

    public function loadMetaData(Request $request){

        $introducers = Admin::where('role', 'introducer')
                    ->orderBy('fullname')
                    ->get();

        return response()->json([
            'introducers' => [
                'data' => $introducers
            ]
        ]);
    }


    public function createBugReport(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'name' => 'required|string|max:50',
            'email' => 'required|string|max:500',
            'message' => 'required|string|max:1024'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = $request->user();

        if($user->email != $request->email){
            return response()->json([
                'message' => 'user email missmatch'
            ] , 400);
        }

        $report = BugReport::create([
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message
        ]);

        return response()->json([
            'message' => 'Bug report created successfully!',
            'report' => $report
        ]);

    }

    public function getBugReports(Request $request){

        $reports = BugReport::paginate(15);

        return response()->json([
            'reports' => $reports
        ]);


    }


}
