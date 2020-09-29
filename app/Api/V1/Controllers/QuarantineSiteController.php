<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\QuarantineSite;
use DB;

class QuarantineSiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BlankRequest $request)
    {
        $quarantine_site = json_decode($request->input('quarantine_site'));
        $id = DB::connection('covid')->table('quarantine_sites')->insertGetId(get_object_vars($quarantine_site));
        return ['id' => $id];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\QuarantineSite  $quarantineSite
     * @return \Illuminate\Http\Response
     */
    public function show(QuarantineSite $quarantineSite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\QuarantineSite  $quarantineSite
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, QuarantineSite $quarantineSite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\QuarantineSite  $quarantineSite
     * @return \Illuminate\Http\Response
     */
    public function destroy(QuarantineSite $quarantineSite)
    {
        //
    }
}
