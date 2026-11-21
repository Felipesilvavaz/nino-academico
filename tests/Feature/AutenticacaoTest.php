<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** UC01 – Autenticar usuário */
class AutenticacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_usuario_ativo_consegue_entrar(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_senha_errada_nao_autentica(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'errada'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_usuario_inativo_nao_consegue_entrar(): void
    {
        $user = User::factory()->inativo()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
