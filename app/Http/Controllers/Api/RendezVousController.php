<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Duree;
use App\Models\HoraireDeTravail;
use App\Models\RendezVous;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
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
        /*
        *
        * Cette fonctionnalité sera à refactoriser, en créant une classe pour plus de clarté.
        * J'ai déjà préparé un dossier 'classes' dans /App
        *
        */
        if ($request->date != null && $request->idService != null && $request->idPersonnel != null && $request->idDuree != null) {
            /*
            * Vérification des horaires disponibles du personnel
            */
            $horaires = HoraireDeTravail::where('idPersonnel', '=', $request->idPersonnel)
                ->where('jour', '=', date('w', strtotime($request->date)))
                ->get();
            /*
            * Pas de disponibilité pour cette journée, retourne un json vide
            * $horaires[0] car peu importe le nombre de plage horaire pour cette journée, ce sera toujours le même numéro de jour
            */
            if ($horaires->count() > 0) {
                if ($horaires[0]->jour != date('w', strtotime($request->date))) {
                    return response()->json([]);
                }
                /*
            * Recherche de la durée selon la variable idDuree
            */
                $duree = Duree::where('id', '=', $request->idDuree)
                    ->select('duree')
                    ->first();
                /*
            * Nouvelle approche avec Carbon, bug ici si la plage horaire commence à la même heure qu'un horaire invalide...
            * Exemple pris là https://stackoverflow.com/questions/74068073/laravel-carbon-get-interval-slots-with-buffer-duration
            */
                /*$serviceDuration = 40; // 40 minutes slots duration
            $serviceBufferDuration = 5; // 5 minutes buffer time between slots
            $invalidTimeIntervals = ['09:00 - 10:00', '12:10 - 12:30']; // invalid time intervals
            $startWorking = "09:00";
            $endWorking =  "13:30";
            
            $start_time = Carbon::parse($startWorking);
            $end_time = Carbon::parse($endWorking);

            $times = [];

            $loop = true;
            while ($loop) {

                foreach ($invalidTimeIntervals as $interval) {
                    $invalidTime = explode(' - ', $interval);
                    $invalidTime[0] = Carbon::parse($invalidTime[0]);
                    $invalidTime[1] = Carbon::parse($invalidTime[1]);

                    $slot_time = $start_time->copy()->addMinutes($serviceDuration);

                    if (
                        $start_time->lessThan($invalidTime[0])
                        && ($slot_time->greaterThan($invalidTime[1])
                            || $slot_time->between($invalidTime[0], $invalidTime[1])
                        )
                    ) {
                        $start_time = Carbon::parse($invalidTime[1]);
                    }
                }

                $slot_time = $start_time->copy()->addMinutes($serviceDuration);

                if ($slot_time->lessThanOrEqualTo($end_time)) {
                    $times[] =  $start_time->format('H:i') . ' - ' . $slot_time->format('H:i');
                    $start_time = $slot_time->addMinutes($serviceBufferDuration);
                    continue;
                }

                $loop = false;
            }

            print_r($times);*/

                /*
            * Fonction qui va générer toutes les plages horaires
            */
                function getTimeSlots($duration, $startTime, $endTime, $tranchesHoraires)
                {
                    //
                    $start = new DateTime($startTime);
                    $end = new DateTime($endTime);

                    $interval = new DateInterval("PT" . $duration . "M");
                    $period = new DatePeriod($start, $interval, $end);

                    $slots = array();
                    $slot_counter = 0;
                    foreach ($period as $dt) {
                        $slots[] = $dt;
                    }
                    foreach ($slots as $key => $dt) {
                        $slot_counter++;
                        if ($slot_counter == count($slots)) {
                            $current = $end;
                        } else if ($slot_counter <= count($slots)) {
                            $current = $slots[$key + 1];
                        }
                        $previous = $slots[$key];
                        if ($previous->format('H:i'))
                            array_push($tranchesHoraires, array('heureDebut' => $previous->format('H:i'), 'heureFin' => $current->format('H:i')));
                    }
                    return $tranchesHoraires;
                }
                /*
            * Génération des 'time slots' selon l'horaire de travail du personnel et découpé selon le choix de la durée du service choisie (30 min, 60...)
            */
                $tranchesHoraires = array();
                foreach ($horaires as $value) {
                    $tranchesHoraires = getTimeSlots($duree->duree, $value->heureDebut, $value->heureFin, $tranchesHoraires);
                    /*
                * Vérification du dernier élément généré (bug: exemple si la durée est de 1 heure et que la plage horaire se termine à 16:30, il va générer 1 entrée de 16h00 à 16h30)
                */
                    $verif1 = new DateTime($tranchesHoraires[count($tranchesHoraires) - 1]['heureDebut']);
                    $verif2 = new DateTime($tranchesHoraires[count($tranchesHoraires) - 1]['heureFin']);
                    if ($verif1->diff($verif2)->format('%i') < $duree->duree) array_pop($tranchesHoraires);
                }
                /*
            * Vérification des rendez-vous déjà présents pour cette date de ce personnel et de ce service passé dans la request
            * Va aussi vérifier si le personnel a posé un horaire de non disponibilité pour cette journée (dans la table rendezVous, idClient à null)
            */
                $rdv = RendezVous::where('idPersonnel', '=', $request->idPersonnel)
                    ->where('idService', '=', $request->idService)
                    ->where('date', '=', $request->date)
                    ->select('heureDebut', 'heureFin')
                    ->get();
                /*
            * S'il existe des contraintes il faut les retirer du tableau tranchesHoraires (un tableau final sera utilisé pour ne garder que les plages horaires disponibles)
            */
                if ($rdv->count() > 0) {
                    /*
                * Fonction pour trouver les plages horaires qui posent problème
                * $heureDebutHoraire -> Représente l'heure de début de la plage horaire du tableau tranchesHoraires
                * $heureFinHoraire -> Représente l'heure de fin de la plage horaire du tableau tranchesHoraires
                * $heureDebutRDV -> Représente l'heure de début présent dans la table RDV et qui peut entre en collision avec le tableau tranchesHoraires
                * $heureFinRDV -> Représente l'heure de fin présent dans la table RDV et qui peut entre en collision avec le tableau tranchesHoraires
                *
                * Retourne true si les tranches horaires passées en paramètre ne rentrent pas en conflit
                */
                    function conflit($heureDebutHoraire, $heureFinHoraire, $heureDebutRDV, $heureFinRDV)
                    {
                        $heureDebutHoraire = getTimeInSeconds($heureDebutHoraire);
                        $heureFinHoraire = getTimeInSeconds($heureFinHoraire);
                        $heureDebutRDV = getTimeInSeconds($heureDebutRDV);
                        $heureFinRDV = getTimeInSeconds($heureFinRDV);
                        return $heureFinHoraire <= $heureDebutRDV || $heureDebutHoraire >= $heureFinRDV;
                    }
                    /*
                * Fonction qui permet de transformer en secondes une heure:minutes passé en paramètre
                */
                    function getTimeInSeconds($time)
                    {
                        $time = explode(":", $time);
                        return intval($time[0]) * 3600 + intval($time[1]) * 60;
                    }
                    /*
                * Boucle sur toutes tranches horaires
                * le tableau tranchesHorairesFinales va contenir la liste des horaires disponibles réelles
                */
                    $tranchesHorairesFinales = array();
                    foreach ($tranchesHoraires as $value) {
                        /*
                    * Boule sur la plage des RDV déjà existants
                    */
                        $flag = true;
                        foreach ($rdv as $value1) {
                            if ($flag) {
                                (conflit($value['heureDebut'], $value['heureFin'], $value1->heureDebut, $value1->heureFin)) ? $flag = true : $flag = false;
                            }
                        }
                        /*
                    * Si la variable bool $flag est vraie alors il n'y a pas de conflit pour cette plage horaire, on l'ajoute au tableau tranchesHorairesFinales
                    */
                        if ($flag) $tranchesHorairesFinales[] = array('heureDebut' => $value['heureDebut'], 'heureFin' => $value['heureFin']);
                    }
                    return response()->json($tranchesHorairesFinales);
                } else {
                    /*
                * Pas de RDV existants, on retourne le tableau tranchesHoraires qui a été généré selon la durée demandé
                */
                    return response()->json($tranchesHoraires);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucune disponibilité'
                ], 200);
            }
        } else {
            try {
                $reservations = RendezVous::join('personnel', 'rendezVous.idPersonnel', 'personnel.id')
                    ->join('utilisateur', 'personnel.idUtilisateur', 'utilisateur.id')
                    ->join('service', 'rendezVous.idService', 'service.id')
                    ->join('duree', 'rendezVous.idDuree', 'duree.id')
                    ->where('rendezVous.idClient', $request->idClient)
                    ->where('rendezVous.etat', 1)
                    ->select('rendezVous.*', DB::raw("DATE_FORMAT(rendezVous.heureDebut, '%H:%i') as heureDebutl"), 'utilisateur.prenom', 'utilisateur.nom', 'duree.duree', 'duree.prix')
                    ->get();

                return response()->json([
                    'status' => true,
                    'reservations' => $reservations
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
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
        if ($request->date != null && $request->idService != null && $request->idPersonnel != null && $request->idDuree != null && $request->heureDebut == null && $request->heureFin == null) {
            /*
            * Vérification des horaires disponibles du personnel
            */
            $horaires = HoraireDeTravail::where('idPersonnel', '=', $request->idPersonnel)
                ->where('jour', '=', date('w', strtotime($request->date)))
                ->get();
            /*
            * Pas de disponibilité pour cette journée, retourne un json vide
            * $horaires[0] car peu importe le nombre de plage horaire pour cette journée, ce sera toujours le même numéro de jour
            */
            if ($horaires->count() > 0) {
                if ($horaires[0]->jour != date('w', strtotime($request->date))) {
                    return response()->json([]);
                }
                /*
            * Recherche de la durée selon la variable idDuree
            */
                $duree = Duree::where('id', '=', $request->idDuree)
                    ->select('duree')
                    ->first();
                /*
            * Nouvelle approche avec Carbon, bug ici si la plage horaire commence à la même heure qu'un horaire invalide...
            * Exemple pris là https://stackoverflow.com/questions/74068073/laravel-carbon-get-interval-slots-with-buffer-duration
            */
                /*$serviceDuration = 40; // 40 minutes slots duration
            $serviceBufferDuration = 5; // 5 minutes buffer time between slots
            $invalidTimeIntervals = ['09:00 - 10:00', '12:10 - 12:30']; // invalid time intervals
            $startWorking = "09:00";
            $endWorking =  "13:30";
            
            $start_time = Carbon::parse($startWorking);
            $end_time = Carbon::parse($endWorking);

            $times = [];

            $loop = true;
            while ($loop) {

                foreach ($invalidTimeIntervals as $interval) {
                    $invalidTime = explode(' - ', $interval);
                    $invalidTime[0] = Carbon::parse($invalidTime[0]);
                    $invalidTime[1] = Carbon::parse($invalidTime[1]);

                    $slot_time = $start_time->copy()->addMinutes($serviceDuration);

                    if (
                        $start_time->lessThan($invalidTime[0])
                        && ($slot_time->greaterThan($invalidTime[1])
                            || $slot_time->between($invalidTime[0], $invalidTime[1])
                        )
                    ) {
                        $start_time = Carbon::parse($invalidTime[1]);
                    }
                }

                $slot_time = $start_time->copy()->addMinutes($serviceDuration);

                if ($slot_time->lessThanOrEqualTo($end_time)) {
                    $times[] =  $start_time->format('H:i') . ' - ' . $slot_time->format('H:i');
                    $start_time = $slot_time->addMinutes($serviceBufferDuration);
                    continue;
                }

                $loop = false;
            }

            print_r($times);*/

                /*
            * Fonction qui va générer toutes les plages horaires
            */
                function getTimeSlots($duration, $startTime, $endTime, $tranchesHoraires)
                {
                    //
                    $start = new DateTime($startTime);
                    $end = new DateTime($endTime);

                    $interval = new DateInterval("PT" . $duration . "M");
                    $period = new DatePeriod($start, $interval, $end);

                    $slots = array();
                    $slot_counter = 0;
                    foreach ($period as $dt) {
                        $slots[] = $dt;
                    }
                    foreach ($slots as $key => $dt) {
                        $slot_counter++;
                        if ($slot_counter == count($slots)) {
                            $current = $end;
                        } else if ($slot_counter <= count($slots)) {
                            $current = $slots[$key + 1];
                        }
                        $previous = $slots[$key];
                        if ($previous->format('H:i'))
                            array_push($tranchesHoraires, array('heureDebut' => $previous->format('H:i'), 'heureFin' => $current->format('H:i')));
                    }
                    return $tranchesHoraires;
                }
                /*
            * Génération des 'time slots' selon l'horaire de travail du personnel et découpé selon le choix de la durée du service choisie (30 min, 60...)
            */
                $tranchesHoraires = array();
                foreach ($horaires as $value) {
                    $tranchesHoraires = getTimeSlots($duree->duree, $value->heureDebut, $value->heureFin, $tranchesHoraires);
                    /*
                * Vérification du dernier élément généré (bug: exemple si la durée est de 1 heure et que la plage horaire se termine à 16:30, il va générer 1 entrée de 16h00 à 16h30)
                */
                    $verif1 = new DateTime($tranchesHoraires[count($tranchesHoraires) - 1]['heureDebut']);
                    $verif2 = new DateTime($tranchesHoraires[count($tranchesHoraires) - 1]['heureFin']);
                    if ($verif1->diff($verif2)->format('%i') < $duree->duree) array_pop($tranchesHoraires);
                }
                /*
            * Vérification des rendez-vous déjà présents pour cette date de ce personnel et de ce service passé dans la request
            * Va aussi vérifier si le personnel a posé un horaire de non disponibilité pour cette journée (dans la table rendezVous, idClient à null)
            */
                $rdv = RendezVous::where('idPersonnel', '=', $request->idPersonnel)
                    ->where('idService', '=', $request->idService)
                    ->where('date', '=', $request->date)
                    ->select('heureDebut', 'heureFin')
                    ->get();
                /*
            * S'il existe des contraintes il faut les retirer du tableau tranchesHoraires (un tableau final sera utilisé pour ne garder que les plages horaires disponibles)
            */
                if ($rdv->count() > 0) {
                    /*
                * Fonction pour trouver les plages horaires qui posent problème
                * $heureDebutHoraire -> Représente l'heure de début de la plage horaire du tableau tranchesHoraires
                * $heureFinHoraire -> Représente l'heure de fin de la plage horaire du tableau tranchesHoraires
                * $heureDebutRDV -> Représente l'heure de début présent dans la table RDV et qui peut entre en collision avec le tableau tranchesHoraires
                * $heureFinRDV -> Représente l'heure de fin présent dans la table RDV et qui peut entre en collision avec le tableau tranchesHoraires
                *
                * Retourne true si les tranches horaires passées en paramètre ne rentrent pas en conflit
                */
                    function conflit($heureDebutHoraire, $heureFinHoraire, $heureDebutRDV, $heureFinRDV)
                    {
                        $heureDebutHoraire = getTimeInSeconds($heureDebutHoraire);
                        $heureFinHoraire = getTimeInSeconds($heureFinHoraire);
                        $heureDebutRDV = getTimeInSeconds($heureDebutRDV);
                        $heureFinRDV = getTimeInSeconds($heureFinRDV);
                        return $heureFinHoraire <= $heureDebutRDV || $heureDebutHoraire >= $heureFinRDV;
                    }
                    /*
                * Fonction qui permet de transformer en secondes une heure:minutes passé en paramètre
                */
                    function getTimeInSeconds($time)
                    {
                        $time = explode(":", $time);
                        return intval($time[0]) * 3600 + intval($time[1]) * 60;
                    }
                    /*
                * Boucle sur toutes tranches horaires
                * le tableau tranchesHorairesFinales va contenir la liste des horaires disponibles réelles
                */
                    $tranchesHorairesFinales = array();
                    foreach ($tranchesHoraires as $value) {
                        /*
                    * Boule sur la plage des RDV déjà existants
                    */
                        $flag = true;
                        foreach ($rdv as $value1) {
                            if ($flag) {
                                (conflit($value['heureDebut'], $value['heureFin'], $value1->heureDebut, $value1->heureFin)) ? $flag = true : $flag = false;
                            }
                        }
                        /*
                    * Si la variable bool $flag est vraie alors il n'y a pas de conflit pour cette plage horaire, on l'ajoute au tableau tranchesHorairesFinales
                    */
                        if ($flag) $tranchesHorairesFinales[] = array('heureDebut' => $value['heureDebut'], 'heureFin' => $value['heureFin']);
                    }
                    return response()->json($tranchesHorairesFinales);
                } else {
                    /*
                * Pas de RDV existants, on retourne le tableau tranchesHoraires qui a été généré selon la durée demandé
                */
                    return response()->json($tranchesHoraires);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucune disponibilité'
                ], 200);
            }
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
                $reservations = RendezVous::join('personnel', 'rendezVous.idPersonnel', 'personnel.id')
                    ->join('utilisateur', 'personnel.idUtilisateur', 'utilisateur.id')
                    ->join('service', 'rendezVous.idService', 'service.id')
                    ->join('duree', 'rendezVous.idDuree', 'duree.id')
                    ->where('rendezVous.idClient', $request->idClient)
                    ->where('rendezVous.etat', 1)
                    ->select('rendezVous.*', DB::raw("DATE_FORMAT(rendezVous.heureDebut, '%H:%i') as heureDebut"), DB::raw("DATE_FORMAT(rendezVous.heureFin, '%H:%i') as heureFin"), 'utilisateur.prenom', 'utilisateur.nom', 'duree.duree', 'duree.prix', 'service.nomService')
                    ->orderBy('rendezVous.date', 'asc')
                    ->orderBy('rendezVous.heureDebut', 'asc')
                    ->get();
                if ($reservations->count() > 0) {
                    return response()->json([
                        'status' => true,
                        'reservations' => $reservations
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Aucune réservation pour ce client'
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
    public function destroy(RendezVous $rendezVous)
    {
        //
    }
}
