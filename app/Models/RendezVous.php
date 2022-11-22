<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rendezVous extends Model
{
    use HasFactory;

    protected $table = "rendezVous";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'heureDebut',
        'heureFin',
        'etat',
        'reservation',
        'idService',
        'idClient',
        'idPersonnel'
    ];
}
