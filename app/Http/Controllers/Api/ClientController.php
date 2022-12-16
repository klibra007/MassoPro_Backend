<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\RendezVous;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $client = Client::join('utilisateur', 'client.idUtilisateur', 'utilisateur.id')
                ->select('client.id', 'client.estActif', 'utilisateur.prenom', 'utilisateur.nom', 'utilisateur.courriel')
                ->get();
            return response()->json([
                'status' => true,
                'clients' => $client
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
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
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            if (Client::where('id', $id)->exists()) {

                $client = Client::join('utilisateur', 'client.idUtilisateur', 'utilisateur.id')
                    ->where('client.id', $id)
                    ->select('utilisateur.prenom', 'utilisateur.nom', 'utilisateur.courriel', 'utilisateur.telephone', 'client.id AS idClient', 'client.estActif', 'client.dateDeNaissance', 'client.numeroAssuranceMaladie', 'client.contactParSMS', 'client.contactParCourriel')
                    ->first();
                if (!$client->estActif) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Ce client n\'est plus actif.'
                    ], 401);
                }
                return response()->json([
                    'status' => true,
                    'client' => $client
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce client n\'existe pas.'
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
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function edit(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            if (Client::where('id', $id)->exists()) {

                $client = Client::find($id);
                $utilisateur = Utilisateur::find($client->idUtilisateur);

                //Validated
                $validateClient = Validator::make(
                    $request->all(),
                    [
                        'courriel' => 'email|unique:utilisateur,courriel'
                    ]
                );

                if ($validateClient->fails()) {
                    // Le courriel existe déjà, modification du compte courriel impossible
                    $request->courriel = null;
                    /*return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateClient->errors()
                    ], 401);*/
                }

                if ($request->nom != null) $utilisateur->nom = $request->nom;
                if ($request->prenom != null) $utilisateur->prenom = $request->prenom;
                if ($request->courriel != null) $utilisateur->courriel = $request->courriel;
                // À remettre lorsque le bug sur le hash aura été résolu
                if ($request->motDePasse != null) $utilisateur->motDePasse = Hash::make($request->motDePasse);
                //if ($request->motDePasse != null) $utilisateur->motDePasse = $request->motDePasse;
                if ($request->telephone != null) $utilisateur->telephone = $request->telephone;

                $utilisateur->save();

                if ($request->contactParSMS != null) $client->contactParSMS = $request->contactParSMS;
                if ($request->contactParCourriel != null) $client->contactParCourriel = $request->contactParCourriel;

                $client->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Modification effectuée avec succès.'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce client n\'existe pas.'
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
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            if (Client::where('id', $id)->exists()) {
                // Désactivation des enregistrements dans la table rendezVous concernés par ce client
                RendezVous::where('idClient', $id)->update(array('etat' => 0));
                // Désactivation du client
                Client::where('id', $id)->update(array('estActif' => 0));
                return response()->json([
                    'status' => true,
                    'message' => 'Le client et les rendez-vous associés ont été désactivés.'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce client n\'existe pas.'
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
