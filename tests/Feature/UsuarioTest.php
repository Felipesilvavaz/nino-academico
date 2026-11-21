<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** UC02 – Manter usuários */
class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrador_cadastra_usuario(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/users', [
            'name' => 'Novo Técnico', 'email' => 'novo@nino.test', 'perfil' => 'tecnico', 'ativo' => '1',
            'password' => 'segredo123', 'password_confirmation' => 'segredo123',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => 'novo@nino.test', 'perfil' => 'tecnico']);
    }

    public function test_tecnico_nao_acessa_usuarios(): void
    {
        $this->actingAs(User::factory()->create())->get('/users')->assertForbidden();
    }

    public function test_exclusao_inativa_o_usuario(): void
    {
        $admin = User::factory()->admin()->create();
        $alvo = User::factory()->create();

        $this->actingAs($admin)->delete("/users/{$alvo->id}");

        $this->assertFalse($alvo->fresh()->ativo);
    }
}
