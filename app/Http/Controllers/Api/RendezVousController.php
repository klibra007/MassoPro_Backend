<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Duree;
use App\Models\HoraireDeTravail;
use App\Models\Personnel;
use App\Models\RendezVous;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RendezVousController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
        // POST pour connaitre les disponibilités horaires selon une date, un service, un personnel et une durée précise
        if ($request->date != null && $request->idService != null && $request->idPersonnel != null && $request->idDuree != null && $request->heureDebut == null && $request->heureFin == null) {

            if (!Service::where('id', $request->idService)->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce service n\'existe pas.'
                ], 401);
            }

            if (!Personnel::where('id', $request->idPersonnel)->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce personnel n\'existe pas.'
                ], 401);
            }

            /*
            * Vérification des horaires disponibles du personnel pour le service concerné
            */
            $horaires = HoraireDeTravail::join('service_personnel', 'horaireDeTravail.idPersonnel', 'service_personnel.idPersonnel')
                ->where('horaireDeTravail.idPersonnel', $request->idPersonnel)
                ->where('horaireDeTravail.jour', date('w', strtotime($request->date)))
                ->where('service_personnel.idService', $request->idService)
                ->get();

            if ($horaires->count() > 0) {
                /*
                * Recherche de la durée selon la variable idDuree
                */
                if (!Duree::where('id', $request->idDuree)->exists()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Cette durée n\'existe pas.'
                    ], 401);
                }

                $duree = Duree::where('id', '=', $request->idDuree)
                    ->select('duree')
                    ->first();
                /*
                * Nouvelle approche avec Carbon, bug ici si la plage horaire commence à la même heure qu'un horaire invalide...
                * Bug trouvé lessThanOrEqualTo au lieu de lessThan
                * Exemple pris là https://stackoverflow.com/questions/74068073/laravel-carbon-get-interval-slots-with-buffer-duration
                */
                $serviceDuration = $duree->duree; // Durée
                $serviceBufferDuration = 0; // S'il devait y avoir un temps d'arrêt entre chaque plage horaire

                // Recherche des heures indisponibles dans les rendez-vous
                $invalidTimeIntervals = [];
                $rendezVous = RendezVous::where('idPersonnel', '=', $request->idPersonnel)
                    ->where('idService', '=', $request->idService)
                    ->where('date', '=', $request->date)
                    ->select('heureDebut', 'heureFin')
                    ->get();
                foreach ($rendezVous as $rdv) {
                    array_push($invalidTimeIntervals, substr($rdv->heureDebut, 0, 5) . ' - ' . substr($rdv->heureFin, 0, 5));
                }
                //$invalidTimeIntervals = ['09:00 - 10:00', '12:10 - 12:30']; // invalid time intervals

                // Pour chaque horaire de travail (9h00 - 12-00 && 14h00 - 17h00) on cherche les disponibilités à renvoyer
                $tranchesHorairesFinales = []; // Tableau des disponibilités

                foreach ($horaires as $horaire) {
                    $startWorking = substr($horaire->heureDebut, 0, 5); // Heure du début de travail
                    $endWorking = substr($horaire->heureFin, 0, 5); // Heure de fin de travail

                    $start_time = Carbon::parse($startWorking);
                    $end_time = Carbon::parse($endWorking);

                    $loop = true;
                    while ($loop) {

                        foreach ($invalidTimeIntervals as $interval) {
                            $invalidTime = explode(' - ', $interval);
                            $invalidTime[0] = Carbon::parse($invalidTime[0]);
                            $invalidTime[1] = Carbon::parse($invalidTime[1]);

                            $slot_time = $start_time->copy()->addMinutes($serviceDuration);

                            if (
                                $start_time->lessThanOrEqualTo($invalidTime[0])
                                && ($slot_time->greaterThan($invalidTime[1])
                                    || $slot_time->between($invalidTime[0], $invalidTime[1])
                                )
                            ) {
                                $start_time = Carbon::parse($invalidTime[1]);
                            }
                        }

                        $slot_time = $start_time->copy()->addMinutes($serviceDuration);

                        if ($slot_time->lessThanOrEqualTo($end_time)) {
                            $tranchesHorairesFinales[] = array('heureDebut' => $start_time->format('H:i'), 'heureFin' => $slot_time->format('H:i'));
                            $start_time = $slot_time->addMinutes($serviceBufferDuration);
                            continue;
                        }

                        $loop = false;
                    }
                }
                return response()->json($tranchesHorairesFinales, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucune disponibilité'
                ], 200);
            }
            // POST avec le choix des horaires
        } elseif ($request->heureDebut != null && $request->heureFin != null) {
            try {
                //Validated
                $validateRDV = Validator::make(
                    $request->all(),
                    [
                        'date' => 'required|date',
                        'heureDebut' => 'required',
                        'heureFin' => 'required',
                        'idService' => 'required|integer|exists:service,id',
                        'idDuree' => 'required|integer|exists:duree,id',
                        'idPersonnel' => 'required|integer|exists:personnel,id'
                    ]
                );

                if ($validateRDV->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateRDV->errors()
                    ], 401);
                }

                if (RendezVous::where('date', $request->date)
                    ->where('heureDebut', $request->heureDebut)
                    ->where('heureFin', $request->heureFin)
                    ->where('etat', 1)
                    ->where('idService', $request->idService)
                    ->where('idDuree', $request->idDuree)
                    ->where('idClient', $request->idClient)
                    ->where('idPersonnel', $request->idPersonnel)
                    ->exists()
                ) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Le rendez-vous existe déjà.'
                    ], 401);
                }

                // Numéro de réservation
                $reservation = random_int(100000000, 999999999);

                $rendezVous = RendezVous::create([
                    'date' => $request->date,
                    'heureDebut' => $request->heureDebut,
                    'heureFin' => $request->heureFin,
                    'etat' => '1',
                    'reservation' => $reservation,
                    'idService' => $request->idService,
                    'idDuree' => $request->idDuree,
                    'idClient' => $request->idClient,
                    'idPersonnel' => $request->idPersonnel
                ]);

                return response()->json([
                    'status' => true,
                    'reservation' => $reservation,
                    'message' => 'Rendez-vous créé avec succès.'
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } else {
            try {
                if ($request->idClient != null) {
                    $rendezVous = RendezVous::join('personnel', 'rendezVous.idPersonnel', 'personnel.id')
                        ->join('utilisateur', 'personnel.idUtilisateur', 'utilisateur.id')
                        ->where('rendezVous.idClient', $request->idClient);
                } else {
                    $rendezVous = RendezVous::join('client', 'rendezVous.idClient', 'client.id')
                        ->join('utilisateur', 'client.idUtilisateur', 'utilisateur.id')
                        ->where('rendezVous.idPersonnel', $request->idPersonnel);
                }
                $query = $rendezVous->join('duree', 'rendezVous.idDuree', 'duree.id')
                    ->join('service', 'rendezVous.idService', 'service.id')
                    ->where('rendezVous.etat', 1)
                    ->select('rendezVous.*', DB::raw("DATE_FORMAT(rendezVous.heureDebut, '%H:%i') as heureDebut"), DB::raw("DATE_FORMAT(rendezVous.heureFin, '%H:%i') as heureFin"), 'utilisateur.prenom', 'utilisateur.nom', 'duree.duree', 'duree.prix', 'service.nomService')
                    ->orderBy('rendezVous.date', 'asc')
                    ->orderBy('rendezVous.heureDebut', 'asc')
                    ->get();

                if ($query->count() > 0) {
                    return response()->json([
                        'status' => true,
                        'reservations' => $query
                    ], 200);
                } else {
                    $message = '';
                    ($request->idClient != null) ? $message = 'Aucune réservation pour ce client' : $message = 'Aucune réservation pour ce personnel';
                    return response()->json([
                        'status' => false,
                        'message' => $message
                    ], 200);
                }
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RendezVous  $rendezVous
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $reservation = RendezVous::join('personnel', 'rendezVous.idPersonnel', 'personnel.id')
                ->join('utilisateur', 'personnel.idUtilisateur', 'utilisateur.id')
                ->join('service', 'rendezVous.idService', 'service.id')
                ->join('duree', 'rendezVous.idDuree', 'duree.id')
                ->where('rendezVous.id', $id)
                ->select('rendezVous.*', 'utilisateur.prenom', 'utilisateur.nom', 'duree.duree', 'duree.prix')
                ->get();
            if ($reservation->count() > 0) {
                return response()->json([
                    'status' => true,
                    'reservation' => $reservation
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucune réservation pour cet id'
                ], 200);
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
     * @param  \App\Models\RendezVous  $rendezVous
     * @return \Illuminate\Http\Response
     */
    public function edit(RendezVous $rendezVous)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RendezVous  $rendezVous
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RendezVous $rendezVous)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RendezVous  $rendezVous
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        try {
            if (RendezVous::where('id', $id)->where('etat', '1')->exists() && Personnel::where('id', $request->idPersonnel)->exists()) {
                // Désactivation de l'enregistrement dans la table rendezVous
                RendezVous::where('id', $id)->update(array('etat' => 0));
                return response()->json([
                    'status' => true,
                    'message' => 'Le rendez-vous a été désactivé.'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Ce rendez-vous n\'existe pas.'
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
