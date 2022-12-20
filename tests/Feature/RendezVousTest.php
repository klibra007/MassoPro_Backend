<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RendezVousTest extends TestCase
{
    public function test_post_rendezvous_idService_false()
    {
        $data = [
            'date' => '2022-12-22',
            'idService' => '999999',
            'idPersonnel' => '1',
            'idDuree' => '1'
        ];

        $response = $this->postJson('/api/rendezvous', $data);
        $response
            ->assertStatus(401)
            ->assertExactJson([
                'status' => false,
                'message' => 'Ce service n\'existe pas.'
            ]);
    }
    public function test_post_rendezvous_idPersonnel_false()
    {
        $data = [
            'date' => '2022-12-22',
            'idService' => '1',
            'idPersonnel' => '99999',
            'idDuree' => '1'
        ];

        $response = $this->postJson('/api/rendezvous', $data);
        $response
            ->assertStatus(401)
            ->assertExactJson([
                'status' => false,
                'message' => 'Ce personnel n\'existe pas.'
            ]);
    }
    public function test_post_rendezvous_idDuree_false()
    {
        $data = [
            'date' => '2022-12-22',
            'idService' => '1',
            'idPersonnel' => '1',
            'idDuree' => '99999'
        ];

        $response = $this->postJson('/api/rendezvous', $data);
        $response
            ->assertStatus(401)
            ->assertExactJson([
                'status' => false,
                'message' => 'Cette durée n\'existe pas.'
            ]);
    }
    public function test_post_rendezvous_aucune_plage_horaire_disponible()
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
    public function test_post_rendezvous_plage_horaire_disponible()
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
