<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RendezVous;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $services = Service::select('id', 'nomService', 'description', 'estActif')
            ->get();
        return response()->json($services);
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
        try {
            if ($request->nomService != null && $request->description != null && $request->idAdministrateur != null) {
                if (Service::create([
                    'nomService' => $request->nomService,
                    'description' => $request->description,
                    'estActif' => '1',
                    'idAdministrateur' => $request->idAdministrateur
                ])) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Service créé avec succès.'
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Un problème est survenu lors de l\'enregistrement dans la base de données.'
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Tous les champs requis ne sont pas complets.'
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    //public function show(Service $service)
    public function show($id)
    {
        try {
            if (Service::where('id', $id)->exists()) {

                $service = Service::where('id', $id)
                    ->select('id', 'nomService', 'description', 'estActif', 'idAdministrateur')
                    ->first();
                return response()->json([
                    'status' => true,
                    'service' => $service
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce service n\'existe pas.'
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            if (Service::where('id', $id)->exists()) {

                $service = Service::find($id);

                if ($request->nomService != null) $service->nomService = $request->nomService;
                if ($request->description != null) $service->description = $request->description;

                $service->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Modification effectuée avec succès.'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce service n\'existe pas.'
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            if (Service::where('id', $id)->exists()) {
                // Désactivation des enregistrements dans la table rendezVous concernés par ce service
                RendezVous::where('idService', $id)->update(array('etat' => 0));
                // Désactivation du service
                Service::where('id', $id)->update(array('estActif' => 0));
                return response()->json([
                    'status' => true,
                    'message' => 'Le service et les rendez-vous associés ont été désactivés.'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce service n\'existe pas.'
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
