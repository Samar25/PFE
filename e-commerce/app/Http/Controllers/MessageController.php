<?php

namespace App\Http\Controllers;
use Auth;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $messages=Message::paginate(20);
        return view('backend.message.index')->with('messages',$messages);
    }
    public function messageFive()
    {
        $message=Message::whereNull('read_at')->limit(5)->get();
        return response()->json($message);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $contact = new Contact();
        $contact->fill([
            'name' => $request->request->get('name'),
            'subject' => $request->request->get('subject'),
            'email' => $request->request->get('email'),
            'phone' => $request->request->get('phone'),
            'message' => $request->request->get('message')
        ]);
        
        $contact->save();

        if($request->request->get('message') != "") {
            file_put_contents(base_path() . '/resources/views/mails/contact.blade.php', $request->request->get('message'));
        }

        app('App\Http\Controllers\MailController')->sendEmail(
            $request->request->get('name'),
            "hatembenhamzacrk09@gmail.com",
            "Admin contact form | " . $request->request->get('subject'),
            [],
            "mails.contact"
        );

        exit();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        $message=Message::find($id);
        if($message){
            $message->read_at=\Carbon\Carbon::now();
            $message->save();
            return view('backend.message.show')->with('message',$message);
        }
        else{
            return back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $message=Message::find($id);
        $status=$message->delete();
        if($status){
            request()->session()->flash('success','Successfully deleted message');
        }
        else{
            request()->session()->flash('error','Error occurred please try again');
        }
        return back();
    }
}
