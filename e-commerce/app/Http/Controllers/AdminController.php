<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Settings;
use App\Models\Order;
use App\Models\Ticket;
use App\User;
use App\Rules\MatchOldPassword;
use Hash;
use DB;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Models\Subscriber;

class AdminController extends Controller
{
    public function index(){
        $data = User::select(\DB::raw("COUNT(*) as count"), \DB::raw("DAYNAME(created_at) as day_name"), \DB::raw("DAY(created_at) as day"))
        ->where('created_at', '>', Carbon::today()->subDay(6))
        ->groupBy('day_name','day')
        ->orderBy('day')
        ->get();
     $array[] = ['Name', 'Number'];
     foreach($data as $key => $value)
     {
       $array[++$key] = [$value->day_name, $value->count];
     }
    //  return $data;
     return view('backend.index')->with('users', json_encode($array));
    }

    public function profile(){
        $profile=Auth()->user();
        // return $profile;
        return view('backend.users.profile')->with('profile',$profile);
    }

    public function profileUpdate(Request $request,$id){
        // return $request->all();
        $user=User::findOrFail($id);
        $data=$request->all();
        $status=$user->fill($data)->save();
        if($status){
            request()->session()->flash('success','Successfully updated your profile');
        }
        else{
            request()->session()->flash('error','Please try again!');
        }
        return redirect()->back();
    }

    public function settings(){
        $data=Settings::first();
        return view('backend.setting')->with('data',$data);
    }

    public function settingsUpdate(Request $request){
        // return $request->all();
        $this->validate($request,[
            'short_des'=>'required|string',
            'description'=>'required|string',
            'photo' => 'image|mimes:png,jpg,jpeg|max:2048',
            'address'=>'required|string',
            'email'=>'required|email',
            'phone'=>'required|string',
        ]);

        if(!is_null($request->photo)) {
            $imageName = time().'.'.$request->photo->extension();

            $request->photo->move(public_path('public/photos/1/'), "logo.png");
            $data['logo'] = $imageName;
        }

        $data=$request->all();
        // return $data;
        $settings=Settings::first();
        // return $settings;
        $status=$settings->fill($data)->save();
        if($status){
            request()->session()->flash('success','Setting successfully updated');
        }
        else{
            request()->session()->flash('error','Please try again');
        }
        return redirect()->route('admin');
    }

    public function changePassword(){
        return view('backend.layouts.changePassword');
    }
    public function changPasswordStore(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);

        return redirect()->route('admin')->with('success','Password successfully changed');
    }

    // Pie chart
    public function userPieChart(Request $request){
        // dd($request->all());
        $data = User::select(\DB::raw("COUNT(*) as count"), \DB::raw("DAYNAME(created_at) as day_name"), \DB::raw("DAY(created_at) as day"))
        ->where('created_at', '>', Carbon::today()->subDay(6))
        ->groupBy('day_name','day')
        ->orderBy('day')
        ->get();
     $array[] = ['Name', 'Number'];
     foreach($data as $key => $value)
     {
       $array[++$key] = [$value->day_name, $value->count];
     }
    //  return $data;
     return view('backend.index')->with('course', json_encode($array));
    }

    // public function activity(){
    //     return Activity::all();
    //     $activity= Activity::all();
    //     return view('backend.layouts.activity')->with('activities',$activity);
    // }

    public function storageLink(){
        // check if the storage folder already linked;
        if(File::exists(public_path('storage'))){
            // removed the existing symbolic link
            File::delete(public_path('storage'));

            //Regenerate the storage link folder
            try{
                Artisan::call('storage:link');
                request()->session()->flash('success', 'Successfully storage linked.');
                return redirect()->back();
            }
            catch(\Exception $exception){
                request()->session()->flash('error', $exception->getMessage());
                return redirect()->back();
            }
        }
        else{
            try{
                Artisan::call('storage:link');
                request()->session()->flash('success', 'Successfully storage linked.');
                return redirect()->back();
            }
            catch(\Exception $exception){
                request()->session()->flash('error', $exception->getMessage());
                return redirect()->back();
            }
        }
    }

    public function displayNewsletter()
    {
        $subsribers = [];
        foreach(DB::table("subscribers")->get() as $subscriber) {
            $subsribers[] = $subscriber->email;
        }
        return view('backend.layouts.newsletter')->with('subscribers', $subsribers);
    }

    public function sendNewsletter(Request $request)
    {
        $recipients = $request->recipients;
        if(count($recipients)) {
            if($request->template != "") {
                file_put_contents(base_path() . '/resources/views/mails/newsletter.blade.php', $request->template);
            }
            foreach($recipients as $recipient) {
                $user = DB::table('users')->where('email', '=', $recipient)->first();
                app('App\Http\Controllers\MailController')->sendEmail(
                    $user->name,
                    $recipient,
                    $request->subject != "" ? $request->subject : "Newsletter",
                    [],
                    "mails.newsletter"
                );
            }
            return redirect()->back()->with('success','Emails sent successfully !');
        }else{
            return redirect()->back();
        }
    }

    public static function statMostUserEarnings()
    {
        $data = Order::select(\DB::raw("user_id"), \DB::raw("COUNT(*) AS nb_orders"), \DB::raw("SUM(total_amount) AS total_sales"))
            ->groupBy('user_id')
            ->orderBy('total_amount', 'DESC')
            ->get()
            ->take(5)
            ->toArray();

        foreach($data as $key => $stat) {
            $user = User::findOrFail($stat['user_id']);
            $data[$key]['name'] = $user->name;
        }

        return json_encode($data);
    }

    public function displayTickets()
    {
        $tickets = DB::table("tickets")->get();

        return view('backend.tickets.index')->with('tickets', $tickets);
    }

    public function editTicket(Request $request)
    {
        $ticket = Ticket::findOrFail($request->id);
        $ticket->status = $request->status;
        $ticket->save();
        exit();
    }

    public function deleteTicket(Request $request)
    {
        $ticket = Ticket::findOrFail($request->id);
        $ticket->delete();
        exit();
    }
}
