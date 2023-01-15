<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\Service_Personnel;
use Illuminate\Http\Request;

class PersonnelServiceController extends Controller
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
    public function show($idPersonnel)
    {
        try {
            if ($idPersonnel != null && (Personnel::where('id', $idPersonnel)->exists())) {
                $services = Service_Personnel::join('service', 'service_personnel.idService', 'service.id')
                    ->where('service_personnel.idPersonnel', $idPersonnel)
                    ->where('service.estActif', 1)
                    ->select('service.id', 'service.nomService')
                    ->get();
                    if ($services->count() > 0) {
                        return response()->json([
                            'status' => true,
                            'services' => $services
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Aucun service pour ce personnel'
                        ], 200);
                    }                    
                return response()->json($services);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce personnel n\'existe pas.'
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }    }

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
