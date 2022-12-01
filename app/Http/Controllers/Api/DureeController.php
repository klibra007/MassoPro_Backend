<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Duree;
use Illuminate\Http\Request;

class DureeController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Duree  $duree
     * @return \Illuminate\Http\Response
     */
    public function show($idService)
    {
        $durees = Duree::where('idService', '=', $idService)
            ->select('id', 'duree', 'prix', 'idService')
            ->get();
        return response()->json($durees);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Duree  $duree
     * @return \Illuminate\Http\Response
     */
    public function edit(Duree $duree)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Duree  $duree
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Duree $duree)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Duree  $duree
     * @return \Illuminate\Http\Response
     */
    public function destroy(Duree $duree)
    {
        //
    }
}
