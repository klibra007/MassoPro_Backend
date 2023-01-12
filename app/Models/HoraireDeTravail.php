<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoraireDeTravail extends Model
{
    use HasFactory;

    protected $table = "horaireDeTravail";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'jour',
        'idPersonnel',
        'heureDebut',
        'heureFin'
    ];
}
