<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use DB;
use Hash;
use Carbon\Carbon;
use App\Models\Product;

class CustomHandlersController extends Controller
{
    // Reset password
    public function userPasswordReset() {
        $email = "hatembenhamzacrk09@gmail.com";
        $user = DB::table('users')->where('email', '=', $email)->first();
        //Check if the user exists
        if (is_null($user)) {
            return redirect()->back()->withErrors(['email' => trans('User does not exist')]);
        }else{
            //Create Password Reset Token
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => self::quickRandom(),
                'created_at' => Carbon::now()
            ]);
            //Get the token just created above
            $tokenData = DB::table('password_resets')
                ->where('email', $email)->first();

            if ($this->sendResetEmail($email, $tokenData->token)) {
                return redirect()->back()->with('status', trans('A reset link has been sent to your email address.'));
            } else {
                return redirect()->back()->withErrors(['error' => trans('A Network Error occurred. Please try again.')]);
            }
        }
    }

    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    private function sendResetEmail($email, $token)
    {
        //Retrieve the user from the database
        $user = DB::table('users')->where('email', $email)->select('*')->first();
        //Generate, the password reset link. The token generated is embedded in the link
        $link = env('APP_URL') . ':8000/password/reset/' . $token . '?email=' . urlencode($user->email);

        app('App\Http\Controllers\MailController')->sendEmail(
            $user->name,
            $email,
            "Password reset",
            array(
                ['name' => "link", "value" => $link],
            )
        );

        try {
        //Here send the link with CURL with an external email API
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function userPasswordResetValidate(Request $request)
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $password_confirmation = $request->request->get('password_confirmation');
        $token = $request->request->get('token');

        if($password != $password_confirmation) {
            return redirect()->back()->withErrors(['error' => trans('Passwords does not match.')]);
        }else{
            $userToken = DB::table('password_resets')->where('email', '=', $email)->first();

            if($userToken->token != $token || !$this->checkTokenValidity($userToken->created_at)) {
                return redirect()->back()->withErrors(['error' => trans('Invalid token.')]);
            }else{
                $user = User::where('email', $email)->first();
                $user->password = Hash::make($password);
                $user->save();
                return redirect('/user/login')->with('success', 'Password successfuly changed !');
            }
        }
    }

    public function checkTokenValidity($token_created_at) {
        $token_created_at = new \DateTime($token_created_at);
        $token_created_at->format('Y-m-d');

        return $token_created_at = date("Y-m-d");
    }

    public function productSearch(Request $request) {
        echo json_encode(Product::where('title','LIKE', "%" . $request->search . "%")->get()->toArray());
        exit();
    }
}
