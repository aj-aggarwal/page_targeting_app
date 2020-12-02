<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rule;
use Exception;
use URL;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // return view('home');
        return view('page_targeting');
    }

    /**
     * Save Rules Data and Create Snippet
     */
    public function saveData(Request $request)
    {
        $request->validate([
            'message' => 'required',
            'rules' => 'required|array',
        ]);

        try {
            $rule = new Rule;
            $rule->user_id  = auth()->id();
            $rule->token = $this->generateUniqueToken();
            $rule->message = $request->message;
            $rule->rules = $request->rules;
            $rule->save();

            $snippet = '<script src="'.URL::asset('/js/snippet.js').'?id='.$rule->token.'"></script>';

            return response()->json([
                'snippet' => $snippet, 
                'status' => 1
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error_message' => $e->getMessage(), 
                'status' => 0
            ]);
        }
    }

    /********** Private Section ***********/

    /**
     * Generate Unique Token for Snippet
     */
    private function generateUniqueToken($length = 20)
    {
        $characters = array_merge(range(0, 9), range('a', 'z'));
        $random_string = '';

        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[array_rand($characters)];
        }

        $accessToken = Rule::where('token', $random_string)->first();

        while($accessToken)
        {
            for ($i = 0; $i < $length; $i++) {
                $random_string .= $characters[array_rand($characters)];
            }
            $accessToken = Rule::where('token', $random_string)->first();
        }

        return $random_string;
    }
}
