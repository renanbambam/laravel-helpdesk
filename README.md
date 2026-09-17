# Internal Helpdesk

An internal ticketing system: employees open tickets for day-to-day problems and requests, and the
support team triages, assigns and resolves them — with **automatic load balancing** across agents.

The interesting part is not the CRUD. It is the routing rule: when a ticket is opened in automatic
mode, it goes to the agent with the fewest *open* tickets — and deciding what "open" means is a
business decision that had to be made and defended, not guessed.

*Built as a full stack technical challenge.*

---

## Stack, and why each piece

| Layer | Technology | Why |
| --- | --- | --- |
| Backend | **Laravel 13** (PHP 8.3+) | Mature and productive — Eloquent, validation, and an ecosystem that lets a small team move fast. |
| Front/back bridge | **Inertia.js** | Removes the friction of building and versioning a separate REST API just for the front end. Controllers return "pages" with data as props — an SPA experience without maintaining two projects. |
| Frontend | **Vue 3** (`<script setup>`) | Simple, reactive components, integrated with Inertia. |
| Styling | **Tailwind CSS v4** | An organized interface quickly, with no custom CSS and no reinvented components. |
| Database | **MySQL 8.4** | Relational, production-parity, and good under the concurrent access a support team generates. |
| Tests | **Pest** | Lean syntax over PHPUnit; readable and fast (in-memory SQLite). |
| Environment | **Laravel Sail (Docker)** | Runs on anyone's machine with one command, with no PHP/Composer/MySQL installed on the host. |

**Why Inertia specifically.** In a small full stack team, maintaining a REST API *and* a separate
SPA doubles the work — routes, serialization, versioning and authentication in two places. Inertia
keeps a single Laravel monolith serving Vue components: less glue code, faster delivery.

---

## Architecture decisions

Organized so a small team can maintain it — SOLID and DRY without over-engineering:

- **Domain enums** (`App\Enums\TicketPriority`, `TicketStatus`) — valid priorities and statuses live
  in one place, type-safely. Each enum also exposes `options()` to feed the front-end selects, so
  database, backend and frontend share a **single source of truth**.

- **A dedicated Action** (`App\Actions\AssignLeastBusyAgent`) — the auto-assignment rule is isolated
  in a single-responsibility class. That makes it unit-testable and reusable (a future batch
  rebalancing command, say) instead of buried in a controller.

- **Form Requests** (`StoreTicketRequest`, `UpdateTicketRequest`) — validation lives outside the
  controller. `UpdateTicketRequest` extends `StoreTicketRequest`, since the rules are identical.

- **Query scopes on the model** (`Ticket::scopeSearch`, `scopeStatus`, …) — filtering and search stay
  on the model, keeping the controller thin.

- **An API Resource** (`TicketResource`) — centralizes how a ticket is shaped for the front end
  (including priority/status as `{ value, label }`), so the payload isn't assembled twice for the
  list and the detail view.

- **A lean RESTful controller** (`TicketController`) — it only orchestrates: validation to the Form
  Requests, filtering to the scopes, agent selection to the Action, formatting to the Resource.

- **Colour lives in the presentation layer** — the backend sends only the label; badge colour is
  decided on the front end from the enum's stable value, keeping CSS concerns out of PHP.

---

## The business rule: what counts as an "open" ticket

Auto-assignment routes to whoever has the fewest open tickets, so "open" has to be defined.

**Decision:** a ticket counts as open while it is **not yet finished** — status **Open** or **In
progress**. **Resolved** and **Closed** are finished and do **not** count toward an agent's load.

**Why:** the point is balancing *active* work. A resolved or closed ticket consumes nobody's time
any more, so it should not influence who receives the next one. Defining it this way makes "fewest
open tickets" actually mean "lightest current workload".

The rule is centralized in [`TicketStatus::openValues()`](app/Enums/TicketStatus.php) and exercised
by `tests/Feature/AutoAssignmentTest.php` — including the tie-break and the exclusion of finished
tickets from the count.

---

## Running it

Requires **Docker** and **Docker Compose**, with ports **80** and **3306** free. Nothing else needs
to be installed on the host.

```bash
cp .env.example .env

# Install PHP dependencies without PHP on the host
docker run --rm -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php83-composer:latest composer install --ignore-platform-reqs
# On Windows PowerShell, replace $(pwd) with ${PWD}

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install && ./vendor/bin/sail npm run dev
```

The app is then at **http://localhost**.

### Tests

```bash
./vendor/bin/sail artisan test
```

They run against in-memory SQLite — fast and isolated, without touching the development database.
The suite covers CRUD, validation, filtering and search, and the auto-assignment rule.

### Seed data

Four agents and eight tickets, **deliberately unbalanced** (one agent overloaded, one with nothing)
so the effect of automatic assignment is visible immediately: open a new ticket in automatic mode
and it lands on the idle agent.

---

## Project structure

```
app/
├── Actions/AssignLeastBusyAgent.php   # the auto-assignment rule (SRP)
├── Enums/
│   ├── TicketPriority.php             # priorities + options for the front end
│   └── TicketStatus.php               # statuses + the definition of "open"
├── Http/
│   ├── Controllers/TicketController.php
│   ├── Requests/                      # StoreTicketRequest / UpdateTicketRequest
│   └── Resources/TicketResource.php
└── Models/
    ├── Agent.php                      # agent + openTickets() relation
    └── Ticket.php                     # ticket + filter/search scopes

resources/js/
├── Layouts/AppLayout.vue
├── Components/                        # TicketForm, Priority/StatusBadge, Pagination
└── Pages/Tickets/                     # Index, Create, Edit, Show

tests/
├── Feature/                           # TicketManagementTest, AutoAssignmentTest
└── Unit/                              # TicketStatusTest
```

---

## Trade-offs, stated plainly

Scope was kept deliberately narrow in favour of quality:

- **No authentication.** Agents are not users who log in, and the requester is free text. The natural
  next step is auth plus roles (employee vs. support), which would let the requester be filled in
  automatically.
- **No CRUD for agents.** They exist via seed and can be selected. Adding one would be trivial,
  reusing the ticket pattern.
- **MySQL in production, SQLite in tests.** Production parity day to day, speed and isolation in the
  suite. Thanks to Eloquent, switching is an `.env` change.
- **No status history.** Only the current state is stored. Recording the transition history — who
  changed what, and when — is the useful next evolution.

---

## References

[Inertia.js](https://inertiajs.com/) · [Vue 3](https://vuejs.org/) ·
[Tailwind CSS v4](https://tailwindcss.com/) · [Pest](https://pestphp.com/) ·
[Laravel Sail](https://laravel.com/docs/sail) · [Laravel Pint](https://laravel.com/docs/pint)
