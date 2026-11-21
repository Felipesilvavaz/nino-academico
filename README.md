# NINO – Módulo Frango de Corte (versão acadêmica)

Sistema web para **gestão de lotes de frango de corte** em granjas integradas, desenvolvido em **PHP 8.3 + Laravel 11 + MySQL 8**.

Projeto das disciplinas **Projeto de Desenvolvimento de Software I e II** – UniFil – Centro Universitário Filadélfia.

> Esta é uma versão acadêmica e simplificada, inspirada no sistema NINO (Núcleo de Inteligência, Nutrição e Operação). Ela foi escrita do zero para a disciplina e **não contém código nem dados reais da empresa**. Os dados de exemplo (seeders) são fictícios.

## Integrantes

| Nome | RA |
|------|----|
| [NOME DO ALUNO 1] | [000000] |
| [NOME DO ALUNO 2 – remova se for individual] | [000000] |

## Funcionalidades (CRUD)

| Caso de uso | O que faz |
|---|---|
| UC01 – Autenticar usuário | Login e logout por e-mail e senha. Usuários inativos não entram. |
| UC02 – Manter usuários | CRUD de usuários com perfil (administrador, técnico, gestor). A exclusão inativa o usuário. Apenas o administrador acessa. |
| UC03 – Manter granjas e aviários | CRUD de produtores integrados, granjas e aviários. A granja é cadastrada junto com seus aviários, numa transação. |
| UC04 – Manter lotes | Alojar, listar, editar, encerrar e excluir lotes (soft delete). Um aviário só pode ter um lote aberto. |
| UC05 – Registrar acompanhamento diário | CRUD de mortalidade, eliminados, ração, peso e temperatura do lote. |
| UC06 – Consultar desempenho do lote | Relatório com viabilidade, GMD, conversão alimentar e IEP, com filtros e impressão em PDF. |

## Tecnologias

- PHP 8.3 e Laravel 11 (MVC, Eloquent ORM, Form Requests, Gates, migrations e seeders)
- MySQL 8 (SQLite em memória nos testes automatizados)
- Blade, Bootstrap 5 e Bootstrap Icons (via CDN)
- PHPUnit 11

## Como executar

Pré-requisitos: PHP 8.2 ou superior (com as extensões `pdo_mysql` e `pdo_sqlite`), Composer e MySQL 8.

```bash
git clone https://github.com/[usuario]/[repositorio].git nino
cd nino
composer install
cp .env.example .env          # no Windows: copy .env.example .env
php artisan key:generate
```

Crie o banco `nino` no MySQL e ajuste `DB_USERNAME` e `DB_PASSWORD` no `.env`. Depois:

```bash
php artisan migrate --seed
php artisan serve
```

Acesse http://localhost:8000.

### Usuários de demonstração (senha: `password`)

| Perfil | E-mail |
|---|---|
| Administrador | admin@nino.test |
| Técnico de Campo | tecnico@nino.test |
| Gestor | gestor@nino.test |

### Testes automatizados

```bash
php artisan test
```

## Estrutura principal

```
app/
  Enums/StatusLote.php            status do lote (aberto, encerrado, cancelado)
  Http/Controllers/               Auth, Dashboard, User, Produtor, Granja, Aviario, Lote, Acompanhamento, Relatorio
  Http/Requests/                  validações e regras de negócio (RN01 a RN06)
  Models/                         User, Produtor, Granja, Aviario, Lote, Acompanhamento
database/
  migrations/                     estrutura do banco (DER)
  seeders/DatabaseSeeder.php      dados fictícios de demonstração
resources/views/                  telas Blade
routes/web.php                    rotas e controle de acesso por perfil
tests/                            testes de funcionalidade e de unidade
docs/                             documentação do projeto (PDF)
```

## Regras de negócio implementadas

- **RN01:** um aviário só pode ter um lote aberto por vez.
- **RN02:** não é permitido registrar acompanhamento em lote encerrado.
- **RN03:** só pode existir um acompanhamento por lote em cada data.
- **RN04:** mortalidade + eliminados não pode ultrapassar as aves vivas.
- **RN05:** granja ou aviário com lotes vinculados não pode ser excluído.
- **RN06:** só o administrador mantém usuários, e usuários inativos não fazem login.
- **RN07 a RN09:** viabilidade, conversão alimentar e IEP calculados automaticamente (`app/Models/Lote.php`).

## Documentação

A documentação completa, com requisitos, casos de uso, DER, diagrama de atividade, diagrama de classes e diagramas de sequência, está em [`docs/NINO_Documentacao.pdf`](docs/NINO_Documentacao.pdf).
