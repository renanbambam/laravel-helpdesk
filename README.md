# Chamados Internos

Sistema de Controle de Chamados Internos: uma aplicação web onde funcionários
abrem chamados (problemas e solicitações do dia a dia) e a equipe de suporte
acompanha, atribui responsáveis e resolve — com **distribuição automática de
carga** entre os atendentes.

> Projeto desenvolvido como desafio técnico full stack.

---

## Sumário

- [Funcionalidades](#funcionalidades)
- [Stack e justificativas](#stack-e-justificativas)
- [Decisões de arquitetura](#decisões-de-arquitetura)
- [Regra de negócio: o que é um chamado "em aberto"](#regra-de-negócio-o-que-é-um-chamado-em-aberto)
- [Pré-requisitos](#pré-requisitos)
- [Como rodar o projeto](#como-rodar-o-projeto)
- [Rodando os testes](#rodando-os-testes)
- [Dados de exemplo](#dados-de-exemplo)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Trade-offs e próximos passos](#trade-offs-e-próximos-passos)
- [Bibliotecas e referências](#bibliotecas-e-referências)

---

## Funcionalidades

- **CRUD de chamados**: cadastro, edição, listagem e visualização detalhada.
- **Campos do chamado**: título, descrição, prioridade (baixa, média, alta),
  status (aberto, em andamento, resolvido, fechado), responsável, solicitante
  (opcional) e data/hora de abertura.
- **Responsáveis pelo atendimento**: conjunto de responsáveis já disponíveis
  (4 cadastrados via seed) que podem ser atribuídos a um chamado.
- **Distribuição automática**: ao abrir/editar um chamado, é possível deixar o
  sistema escolher automaticamente o responsável com **menos chamados em aberto**,
  ou escolher manualmente.
- **Tela de acompanhamento**: lista com **busca** (título/solicitante),
  **filtros** (status, prioridade, responsável), **ordenação** por coluna e
  **paginação**.

---

## Stack e justificativas

| Camada | Tecnologia | Por quê |
| --- | --- | --- |
| Back-end | **Laravel 13** (PHP 8.3+) | Framework maduro e produtivo, com Eloquent, validação e um ecossistema que acelera o desenvolvimento de um time pequeno. |
| Ponte front/back | **Inertia.js** | Elimina o atrito de construir e versionar uma API REST separada só para o front. As controllers retornam "páginas" e os dados como props — experiência de SPA sem o custo de manter dois projetos. |
| Front-end | **Vue 3** (`<script setup>`) | Componentização simples e reativa, integrada ao Inertia. |
| Estilo | **Tailwind CSS v4** | Permite montar uma interface organizada rapidamente, sem CSS customizado e sem reinventar componentes. |
| Banco | **MySQL 8.4** | Banco relacional robusto, com paridade com produção e bom suporte a acessos concorrentes da equipe de suporte. |
| Testes | **Pest** | Sintaxe enxuta sobre o PHPUnit; testes legíveis e rápidos (SQLite em memória). |
| Ambiente | **Laravel Sail (Docker)** | "Roda na máquina de qualquer um do time" com um comando, sem instalar PHP/Composer/MySQL no host. |

> **Por que Inertia?** A dica do desafio aponta para "reduzir o atrito entre
> front e back". Em um time full stack pequeno, manter uma API REST + um SPA
> separado dobra o trabalho (rotas, serialização, versionamento, autenticação
> em dois lugares). O Inertia resolve isso mantendo um único monolito Laravel
> que serve componentes Vue — menos código de cola, mais velocidade de entrega.

---

## Decisões de arquitetura

O código foi organizado para favorecer manutenção por uma equipe pequena,
seguindo SOLID e DRY sem sobre-engenharia:

- **Enums de domínio** (`App\Enums\TicketPriority`, `TicketStatus`): os valores
  válidos de prioridade e status vivem em um único lugar, com segurança de tipo.
  Cada enum também expõe `options()` para alimentar os selects do front — uma
  **única fonte de verdade** compartilhada entre banco, back-end e front-end.

- **Action dedicada** (`App\Actions\AssignLeastBusyAgent`): a regra da
  distribuição automática é isolada em uma classe com responsabilidade única
  (SRP). Isso a torna testável unitariamente e reaproveitável (ex.: um futuro
  comando de rebalanceamento em lote), em vez de ficar escondida no controller.

- **Form Requests** (`StoreTicketRequest`, `UpdateTicketRequest`): a validação
  fica fora do controller. O `UpdateTicketRequest` herda do `StoreTicketRequest`
  (DRY), já que as regras são idênticas.

- **Query Scopes no model** (`Ticket::scopeSearch`, `scopeStatus`, etc.): a
  lógica de filtro/busca fica no model, deixando o controller fino e legível.

- **API Resource** (`TicketResource`): centraliza a formatação do chamado para o
  front (inclusive prioridade/status como `{ value, label }`), evitando duplicar
  a montagem de payload entre listagem e detalhe.

- **Controller RESTful enxuto** (`TicketController`): apenas orquestra —
  delega validação aos Form Requests, filtros aos scopes, a escolha do
  responsável à Action e a formatação ao Resource.

- **Cores na camada de apresentação**: o back-end envia apenas o rótulo; a cor
  dos badges é decidida no front (a partir do valor estável do enum), mantendo
  regras de CSS fora do PHP.

---

## Regra de negócio: o que é um chamado "em aberto"

O desafio pede para definir e justificar o que conta como "em aberto" na
distribuição automática.

**Decisão:** consideramos **em aberto** todo chamado que ainda **não foi
concluído**, ou seja, com status **Aberto** ou **Em andamento**. Status
**Resolvido** e **Fechado** são considerados concluídos e **não** entram na
contagem de carga.

**Justificativa:** a dor do cliente é equilibrar o trabalho _ativo_ entre os
atendentes. Um chamado resolvido/fechado não consome mais tempo de ninguém,
portanto não deveria pesar na decisão de para quem enviar o próximo chamado.
Assim, "menos chamados em aberto" reflete de fato quem tem menor carga de
trabalho no momento.

Essa regra está centralizada em
[`TicketStatus::openValues()`](app/Enums/TicketStatus.php) e é exercitada pelos
testes em `tests/Feature/AutoAssignmentTest.php`.

---

## Pré-requisitos

- **Docker** e **Docker Compose** (Docker Desktop no Windows/Mac).
- Portas livres: **80** (aplicação) e **3306** (MySQL).

Não é necessário ter PHP, Composer, Node ou MySQL instalados no host — tudo roda
em containers via Laravel Sail.

---

## Como rodar o projeto

> Os comandos abaixo usam `./vendor/bin/sail`. Para encurtar, você pode criar um
> alias: `alias sail='./vendor/bin/sail'`.

### 1. Clonar e entrar na pasta

```bash
git clone <url-do-repositorio>
cd chamados
```

### 2. Criar o arquivo de ambiente

```bash
cp .env.example .env
```

### 3. Instalar as dependências PHP (sem PHP no host)

Como o Composer ainda não rodou, usamos um container só para instalar as
dependências dentro de `vendor/`:

```bash
docker run --rm \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

> No Windows (PowerShell), troque `$(pwd)` por `${PWD}`.

### 4. Subir os containers (aplicação + MySQL)

```bash
./vendor/bin/sail up -d
```

Na primeira execução, a imagem é construída — pode levar alguns minutos.

### 5. Gerar a chave da aplicação, migrar e popular o banco

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

### 6. Instalar dependências do front e compilar os assets

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

> `npm run dev` sobe o Vite em modo de desenvolvimento (hot reload). Para um
> build de produção, use `./vendor/bin/sail npm run build`.

### 7. Acessar

A aplicação fica disponível em **http://localhost** (a raiz redireciona para
`/chamados`).

---

## Rodando os testes

Os testes rodam contra um **SQLite em memória** (rápido e isolado, sem tocar no
banco MySQL de desenvolvimento):

```bash
./vendor/bin/sail artisan test
```

A suíte cobre o CRUD, a validação, os filtros/busca e — com destaque — a regra
de distribuição automática (menor carga, desempate determinístico e a exclusão
de chamados concluídos da contagem).

---

## Dados de exemplo

O seed cria:

- **4 responsáveis**: Ana Souza, Bruno Lima, Carla Mendes e Diego Rocha.
- **8 chamados** inspirados no relato do cliente (computador travando,
  impressora com defeito, cadeira nova, etc.).

A distribuição é **propositalmente desbalanceada** (Ana sobrecarregada, Diego
sem nenhum chamado) para que o efeito da distribuição automática fique evidente:
ao abrir um novo chamado no modo automático, ele tende a cair para o Diego.

---

## Estrutura do projeto

```
app/
├── Actions/
│   └── AssignLeastBusyAgent.php      # Regra da distribuição automática (SRP)
├── Enums/
│   ├── TicketPriority.php            # Prioridades + opções p/ o front
│   └── TicketStatus.php              # Status + definição de "em aberto"
├── Http/
│   ├── Controllers/TicketController.php
│   ├── Middleware/HandleInertiaRequests.php
│   ├── Requests/                     # StoreTicketRequest / UpdateTicketRequest
│   └── Resources/TicketResource.php  # Formatação do chamado p/ o front
└── Models/
    ├── Agent.php                     # Responsável + relação openTickets()
    └── Ticket.php                    # Chamado + scopes de filtro/busca

resources/js/
├── Layouts/AppLayout.vue
├── Components/                       # TicketForm, Priority/StatusBadge, Pagination
└── Pages/Tickets/                    # Index, Create, Edit, Show

database/
├── migrations/                       # agents, tickets
├── factories/                        # AgentFactory, TicketFactory
└── seeders/                          # AgentSeeder, TicketSeeder

tests/
├── Feature/                          # TicketManagementTest, AutoAssignmentTest
└── Unit/                             # TicketStatusTest
```

---

## Trade-offs e próximos passos

Decisões tomadas conscientemente para manter o escopo enxuto e com qualidade
(seguindo a orientação "menos features com alta qualidade"):

- **Sem autenticação/login**: o desafio não exige e os responsáveis não são
  usuários que logam. O campo "solicitante" é um texto livre. Próximo passo
  natural: autenticação + papéis (funcionário vs. suporte), o que permitiria
  preencher o solicitante automaticamente.
- **Responsáveis sem CRUD próprio**: conforme o enunciado, eles existem via seed
  e podem ser selecionados. Adicionar um CRUD seria trivial reaproveitando o
  mesmo padrão dos chamados.
- **MySQL em produção, SQLite nos testes**: produção-paridade no dia a dia e
  velocidade/isolamento na suíte. Graças ao Eloquent, trocar de banco é apenas
  uma mudança no `.env`.
- **Histórico de mudanças de status**: hoje guardamos apenas o estado atual. Uma
  evolução útil seria registrar o histórico (auditoria de quem mudou o quê e
  quando).

---

## Bibliotecas e referências

Além do Laravel e suas dependências padrão:

- [Inertia.js](https://inertiajs.com/) — adaptador `inertiajs/inertia-laravel` e
  cliente `@inertiajs/vue3`.
- [Vue 3](https://vuejs.org/)
- [Tailwind CSS v4](https://tailwindcss.com/) — via `@tailwindcss/vite`.
- [Pest](https://pestphp.com/) — framework de testes.
- [Laravel Sail](https://laravel.com/docs/sail) — ambiente Docker.
- [Laravel Pint](https://laravel.com/docs/pint) — padronização de estilo do código PHP.
