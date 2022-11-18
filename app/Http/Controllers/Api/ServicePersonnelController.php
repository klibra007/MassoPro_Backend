<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service_Personnel;
use Illuminate\Http\Request;

class ServicePersonnelController extends Controller
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
     * @param  \App\Models\Service_Personnel  $service_Personnel
     * @return \Illuminate\Http\Response
     */
    public function show($idService)
    {
        $personnels = Service_Personnel::join('personnel', 'service_personnel.idPersonnel', '=', 'personnel.id')
        ->join('utilisateur', 'personnel.idUtilisateur', '=', 'utilisateur.id')
        ->where('service_personnel.idService', '=', $idService)
        ->where('personnel.typePersonnel', '=', 'Massothérapeute')
        ->select('personnel.id', 'utilisateur.prenom', 'utilisateur.nom')
        ->get();
    	return response()->json([
            'personnels' => $personnels
            ]);   
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Service_Personnel  $service_Personnel
     * @return \Illuminate\Http\Response
     */
    public function edit(Service_Personnel $service_Personnel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service_Personnel  $service_Personnel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Service_Personnel $service_Personnel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Service_Personnel  $service_Personnel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Service_Personnel $service_Personnel)
    {
        //
    }
}
