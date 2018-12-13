<?php

namespace App\Http\Controllers;

use App\Email;
use Illuminate\Http\Request;

class EmailController extends Controller
{

    function __construct()
    {
        parent::__construct();
        if(!in_array(auth()->user()->user_type_id, [10, 12])) abort();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $emails = Email::with(['lab'])->get();
        return view('tables.emails', ['emails' => $emails]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('forms.email');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $email = new Email($request->except(['_token', 'files', 'email_content', 'sending_day', 'sending_hour']));
        $sending_day = $request->input('sending_day');
        $sending_hour = $request->input('sending_hour', 10);
        if($sending_day) $email->time_to_be_sent = $sending_day . ' ' . $sending_hour . ':00:00';
        $email->save();
        $email->save_raw($request->input('email_content'));
        session(['toast_message' => 'The email has been created.']);
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Email  $email
     * @return \Illuminate\Http\Response
     */
    public function show(Email $email)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Email  $email
     * @return \Illuminate\Http\Response
     */
    public function edit(Email $email)
    {
        return view('forms.email', ['email' => $email]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Email  $email
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Email $email)
    {
        $email->fill($request->except(['_token', 'files', '_method', 'email_content', 'sending_day', 'sending_hour']));
        $sending_day = $request->input('sending_day');
        $sending_hour = $request->input('sending_hour', 10);
        if($sending_day) $email->time_to_be_sent = $sending_day . ' ' . $sending_hour . ':00:00';
        if(!$sending_day && $email->time_to_be_sent) $email->time_to_be_sent = null; 
        if($email->time_to_be_sent != $email->getOriginal('time_to_be_sent') && $email->sent) $email->sent = false;
        $email->save();
        $email->save_raw($request->input('email_content'));
        session(['toast_message' => 'The email has been updated.']);
        return redirect('email');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Email  $email
     * @return \Illuminate\Http\Response
     */
    public function destroy(Email $email)
    {
        $email->delete();
        return back();
    }

    public function demo(Email $email)
    {
        return view('forms.send_email', ['email' => $email]);
    }

    public function demo_email(Request $request, Email $email)
    {
        $email->demo_email($request->input('recepient'));
        session(['toast_message' => 'The email was successful']);
        return back();
    }

}
