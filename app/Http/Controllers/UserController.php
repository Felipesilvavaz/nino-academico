<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** UC02 – Manter usuários (somente administrador). */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = User::query()
            ->when($request->filled('busca'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%'.$request->busca.'%')
                ->orWhere('email', 'like', '%'.$request->busca.'%')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('usuarios'));
    }

    public function create(): View
    {
        return view('users.form', ['usuario' => new User(['ativo' => true, 'perfil' => 'tecnico'])]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create([...$request->validated(), 'ativo' => $request->boolean('ativo')]);

        return redirect()->route('users.index')->with('success', 'Usuário cadastrado com sucesso.');
    }

    public function edit(User $user): View
    {
        return view('users.form', ['usuario' => $user]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $dados = [...$request->validated(), 'ativo' => $request->boolean('ativo')];
        if (blank($dados['password'] ?? null)) {
            unset($dados['password']);
        }
        if ($user->is($request->user())) {
            $dados['ativo'] = true; // o administrador não pode se inativar
        }

        $user->update($dados);

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    /** A exclusão é lógica: o usuário é inativado para preservar o histórico. */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Você não pode inativar o próprio usuário.');
        }

        $user->update(['ativo' => ! $user->ativo]);

        return back()->with('success', $user->ativo ? 'Usuário reativado.' : 'Usuário inativado.');
    }
}
