<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RendezVousTest extends TestCase
{
    public function test_post_rendezvous_false()
    {
        $data = [
            'date' => '2022-12-22',
            'idService' => '1',
            'idPersonnel' => '2',
            'idDuree' => '1'
        ];

        $response = $this->postJson('/api/rendezvous', $data);
        $response
            ->assertStatus(200)
            ->assertExactJson([
                'status' => false,
                'message' => 'Aucune disponibilité'
            ]);
    }
    public function test_post_rendezvous_true()
    {
        $data = [
            'date' => '2022-12-22',
            'idService' => '1',
            'idPersonnel' => '1',
            'idDuree' => '1'
        ];

        $response = $this->postJson('/api/rendezvous', $data);
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'heureDebut',
                    'heureFin'
                ]
            ]);
    }
}
