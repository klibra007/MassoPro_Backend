<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Duree;
use App\Models\RendezVous;
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
        $durees = Duree::select('id', 'duree', 'prix', 'estActif')
            ->get();
        return response()->json($durees);
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
            if ($request->duree != null && $request->prix != null) {
                if (Duree::create([
                    'duree' => $request->duree,
                    'prix' => $request->prix,
                    'estActif' => 1
                ])) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Durée créée avec succès.'
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
     * @param  \App\Models\Duree  $duree
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $durees = Duree::where('id', '=', $id)
            ->select('id', 'duree', 'prix', 'estActif')
            ->first();
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
    public function update(Request $request, $id)
    {
        try {
            if (Duree::where('id', $id)->exists()) {

                $duree = Duree::find($id);

                if ($request->duree != null) $duree->duree = $request->duree;
                if ($request->prix != null) $duree->prix = $request->prix;
                if ($request->estActif != null) $duree->estActif = $request->estActif;

                $duree->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Modification effectuée avec succès.'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Cette durée n\'existe pas.'
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
     * @param  \App\Models\Duree  $duree
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            if (Duree::where('id', $id)->exists()) {
                // Désactivation des enregistrements dans la table rendezVous concernés par cette durée
                RendezVous::where('idDuree', $id)->update(array('etat' => 0));
                // Désactivation de la durée
                Duree::where('id', $id)->update(array('estActif' => 0));
                return response()->json([
                    'status' => true,
                    'message' => 'La durée et les rendez-vous associés ont été désactivés.'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Cette durée n\'existe pas.'
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
