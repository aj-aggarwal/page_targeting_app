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
        $currentUserId = auth()->id();
        $rule = Rule::whereUserId($currentUserId)->first();

        $data = [];
        if($rule) {
            $data = [
                'message' => $rule->message,
                'token' => $rule->token,
                'rules' => $rule->rules,
                'rules_count' => count($rule->rules),
            ];
        }
        return view('page_targeting', $data);
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
            $currentUserId = auth()->id();

            $rule = Rule::whereUserId($currentUserId)->first();

            if(!$rule) {
                $rule = new Rule;
                $rule->user_id  = $currentUserId;
                $rule->token = $this->generateUniqueToken();
            }

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

    /**
     * Get alert mesage to show on targeted page by matching rules..
     */
    public function getAlertMessage(Request $request)
    {
        $rule = Rule::whereToken($request->snippet_id)->first();
        $alertMessage = '';

        if($rule) {
            $alertMessage = $this->getMessageByMatchingRules($rule, $request->current_page_path_name);
        }
        return response()->json([
            'alert_message' => $alertMessage,
        ])->header("Access-Control-Allow-Origin",  "*");
    }

    /**
     * A function for testing snippet. It renders test.blade.php view file where we can add snippet to test.
     */
    public function test()
    {
        return view('test');
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

    private function getMessageByMatchingRules($rule, $pathName)
    {
        $message = '';
        $pathName = ltrim($pathName, '/');
        $rules = $rule->rules;

        $dontShow = false;
        $show = false;
        // First check don't show rules
        foreach ($rules as $value) {
           if(!isset($value['constraint']) || $value['constraint'] != 'dont_show') continue;
           $dontShow = true;
           if($this->isRuleMatched($value['rule'], $pathName, $value['value'])) {
                return ''; // Empty Message / No Message
           }
        }

        // Match show message rules..
        foreach ($rules as $value) {
           if(!isset($value['constraint']) || $value['constraint'] == 'dont_show') continue;
           $show = true;
           if($this->isRuleMatched($value['rule'], $pathName, $value['value'])) {
                return $rule->message; //  return message string..
           }
        }

        // Have atleast one show rule..
        if($show) {
            return '';
        }else{
            return $rule->message;
        }
    }

    private function isRuleMatched($rule, $pathName, $matchValue)
    {
        $matchValue = strtolower($matchValue);
        switch ($rule) {
            case 'contains':
                if(stripos($pathName, $matchValue) !== false) return true;
                break;

            case 'start_with':
                if(stripos($pathName, $matchValue) === 0) return true;
                break;

            case 'end_with':
                $len = strlen($matchValue);
                if($len && substr($pathName, -$len) === $matchValue) return true;
                break;

            case 'exact':
                if($pathName == $matchValue) return true;
                break;
            
            default:
                return false;
                break;
        }

        return false;
    }
}
