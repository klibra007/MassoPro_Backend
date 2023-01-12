<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HoraireDeTravail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HoraireDeTravailController extends Controller
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
        try {
            if ($request->jour != null && $request->heureDebut != null && $request->heureFin != null && $request->idPersonnel != null) {
                //Validated
                $validate = Validator::make(
                    $request->all(),
                    [
                        'heureDebut' => 'required',
                        'heureFin' => 'required'
                    ]
                );

                if ($validate->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validate->errors()
                    ], 401);
                }

                // Personnel qui n'a pas d'horaire de travail présent dans la base de données
                if (!HoraireDeTravail::where('jour', $request->jour)
                    ->where('idPersonnel', $request->idPersonnel)
                    ->exists()) {
                    if (HoraireDeTravail::create([
                        'jour' => $request->jour,
                        'heureDebut' => $request->heureDebut,
                        'heureFin' => $request->heureFin,
                        'idPersonnel' => $request->idPersonnel
                    ])) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Horaire de travail créé avec succès.'
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Un problème est survenu lors de l\'enregistrement dans la base de données.'
                        ], 401);
                    }
                    // Personnel qui a déjà au moins une plage horaire définie dans la base de données
                } else {
                    // Recherche des horaires de travail déjà présentes dans la base de données
                    $invalidWorkSchedule = [];
                    $horaireDeTravail = HoraireDeTravail::where('jour', $request->jour)
                        ->where('idPersonnel', '=', $request->idPersonnel)
                        ->select('heureDebut', 'heureFin')
                        ->get();
                    foreach ($horaireDeTravail as $hdt) {
                        array_push($invalidWorkSchedule, substr($hdt->heureDebut, 0, 5) . ' - ' . substr($hdt->heureFin, 0, 5));
                    }
                    $startWorking = substr($request->heureDebut, 0, 5);
                    $endWorking = substr($request->heureFin, 0, 5);

                    // Parse hh:mm en date complète
                    $start_time = Carbon::parse($startWorking);
                    $end_time = Carbon::parse($endWorking);

                    $invalid = false;

                    foreach ($invalidWorkSchedule as $interval) {
                        $invalidTime = explode(' - ', $interval);
                        $invalidTime[0] = Carbon::parse($invalidTime[0]);
                        $invalidTime[1] = Carbon::parse($invalidTime[1]);

                        //
                        if ($start_time->lessThanOrEqualTo($invalidTime[0]) && $end_time->greaterThanOrEqualTo($invalidTime[1]) && !$invalid) $invalid = true;
                        if ($start_time->between($invalidTime[0]->copy()->addMinute(1), $invalidTime[1]->copy()->subMinute(1)) || $end_time->between($invalidTime[0]->copy()->addMinute(1), $invalidTime[1]->copy()->subMinute(1)) && !$invalid) $invalid = true;
                    }
                    if (!$invalid) {
                        if (HoraireDeTravail::create([
                            'jour' => $request->jour,
                            'heureDebut' => $request->heureDebut,
                            'heureFin' => $request->heureFin,
                            'idPersonnel' => $request->idPersonnel
                        ])) {
                            return response()->json([
                                'status' => true,
                                'message' => 'Horaire de travail créé avec succès.'
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
                            'message' => 'L\'horaire demandé pour ce personnel entre en conflit avec un horaire existant.'
                        ], 401);
                    }
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
     * @param  \App\Models\HoraireDeTravail  $horaireDeTravail
     * @return \Illuminate\Http\Response
     */
    public function show(HoraireDeTravail $horaireDeTravail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HoraireDeTravail  $horaireDeTravail
     * @return \Illuminate\Http\Response
     */
    public function edit(HoraireDeTravail $horaireDeTravail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HoraireDeTravail  $horaireDeTravail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HoraireDeTravail $horaireDeTravail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HoraireDeTravail  $horaireDeTravail
     * @return \Illuminate\Http\Response
     */
    public function destroy(HoraireDeTravail $horaireDeTravail)
    {
        //
    }
}
