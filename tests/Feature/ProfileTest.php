<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_not_available(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertNotFound();
    }

    public function test_profile_update_route_is_not_available(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'nama_lengkap' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response->assertNotFound();
    }

    public function test_profile_delete_route_is_not_available(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response->assertNotFound();

        // Pastikan user tidak ikut terhapus
        $this->assertNotNull(
            $user->fresh()
        );
    }
}