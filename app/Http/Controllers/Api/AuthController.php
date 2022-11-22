<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'prenom' => 'required',
                    'nom' => 'required',
                    'courriel' => 'required|email|unique:utilisateur,courriel',
                    'motDePasse' => 'required',
                    'telephone' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $utilisateur = Utilisateur::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'courriel' => $request->courriel,
                'motDePasse' => $request->motDePasse, //Hash::make($request->motDePasse),
                'telephone' => $request->telephone
            ]);
            // Récupération de l'id de l'utilisateur qui vient d'être inséré
            $idUtilisateur = $utilisateur->id;

            $client = Client::create([
                'estActif' => '1',
                'notes' => '',
                'dateDeNaissance' => '1980-01-01',
                'numeroAssuranceMaladie' => '0000000000',
                'contactParSMS' => $request->contactParSMS,
                'contactParCourriel' => $request->contactParCourriel,
                'idUtilisateur' => $idUtilisateur
            ]);
            // Récupération de l'id du client qui vient d'être inséré
            $idClient = $client->id;

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'idClient' => $idClient,
                //'token' => $utilisateur->createToken("API TOKEN")->plainTextToken
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'courriel' => 'required|email',
                    'motDePasse' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            /*if (!Auth::attempt($request->only(['courriel', 'motDePasse']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }*/

            $utilisateur = Utilisateur::join('client', 'utilisateur.id', '=', 'client.idUtilisateur')
                ->where('courriel', $request->courriel)
                ->select('client.id AS idClient')
                ->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'idClient' => $utilisateur->idClient,
                //'token' => $utilisateur->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
