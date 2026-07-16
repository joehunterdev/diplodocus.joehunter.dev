# Database — PostgreSQL

The default for new projects is **PostgreSQL**. It's stricter about types, has first-class JSON (`jsonb`), real enums, and better concurrency behaviour than MySQL — and Laravel supports it with zero friction. MySQL remains a drop-in alternative where the host only offers that.

> **Note** — Switching to MySQL is a two-line change: `DB_CONNECTION=mysql` and `DB_PORT=3306`. Everything below (migrations, seeders, conventions) is identical either way. Hostinger shared plans offer both; pick Postgres unless a constraint forces otherwise.

## Connection config

In `.env` (full template on [Environment & config](06-environment-and-config.md)):

```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=my_app
DB_USERNAME=my_app
DB_PASSWORD=
```

Local Postgres via Docker, if you don't run it natively:

```bash
docker run --name my-app-pg -e POSTGRES_PASSWORD=secret \
  -e POSTGRES_DB=my_app -p 5432:5432 -d postgres:16
```

## Migration best practices

Use the anonymous-class form, lean on foreign-key constraints, and index what you query.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('status')->default('draft'); // string-backed enum
            $table->jsonb('meta')->nullable();           // Postgres jsonb
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

| Practice | Why |
|---|---|
| `foreignId()->constrained()` | Real FK + cascade rules at the DB level |
| String-backed status/enum columns | Portable, readable, cast to a PHP enum in the model |
| `unique()` / composite `index()` | Enforce invariants and keep reads fast |
| `jsonb` for flexible blobs | Queryable JSON; cast to `array`/DTO in the model |
| `timestamps()` + `softDeletes()` | Auditability and safe deletes where it matters |

Cast enums and JSON in the model, never in the controller:

```php
protected function casts(): array
{
    return [
        'status' => PostStatus::class,
        'meta'   => 'array',
    ];
}
```

## Seeder best practices

Three rules keep seeders safe to run anywhere, repeatedly:

1. **Config-driven, never hardcoded.** Credentials and toggles come from `config/seeding.php` (which reads `.env`). See [Environment & config](06-environment-and-config.md).
2. **Idempotent.** Use `updateOrCreate` / `firstOrCreate`, not blind `create`. `migrate:fresh --seed` and re-running against an existing DB both stay clean — no truncation, no duplicates.
3. **Data files live in `database/data/`.** Bulk reference data (CSV/JSON) is read via a small trait, not pasted into PHP.

**The BaseUserSeeder pattern.** One seeder creates all core accounts (super admin, admin, role test users), entirely from `.env` via `config('seeding.*')`. The defining tricks:

- **Skips gracefully** when `SUPER_ADMIN_EMAIL` is unset — a fresh clone seeds without erroring.
- **Env-aware emails:** in local/testing, every extra account is a Gmail `+alias` of the super-admin address (`me+admin@…`, `me+employer@…`) so one inbox receives everything; staging/production use dedicated env addresses.
- **Collect, then insert:** build a collection of user arrays first, then a single `insertUsers()` loop does `updateOrCreate` keyed by email — idempotent and duplicate-proof.
- **All errors logged and re-thrown** to a custom log channel, so a failed seed is greppable.

```php
class BaseUserSeeder extends Seeder
{
    private function seedConfig(string $key, mixed $default = null): mixed
    {
        return config("seeding.{$key}", $default);
    }

    public function run(): void
    {
        if (! $this->seedConfig('super_admin.email')) {
            $this->command->warn('⚠️ SUPER_ADMIN_EMAIL not set in .env - skipping BaseUserSeeder');
            return;
        }

        $isLocalEnv = app()->environment('local', 'testing');
        $superEmail = $this->seedConfig('super_admin.email');

        $usersToCreate = collect([
            [
                'name'     => 'Super Admin',
                'email'    => $superEmail,
                'password' => bcrypt($this->seedConfig('super_admin.password')),
                'role'     => 'SuperAdmin',
            ],
            [
                'name'     => 'Admin User',
                // local: me+admin@gmail.com — one inbox catches all seeded accounts
                'email'    => $isLocalEnv
                    ? str_replace('@', '+admin@', $superEmail)
                    : $this->seedConfig('admin.email'),
                'password' => bcrypt($this->seedConfig('admin.password', 'password')),
                'role'     => 'Admin',
            ],
            // ...one entry per role you need to test as
        ]);

        $usersToCreate->each(fn (array $u) => User::updateOrCreate(
            ['email' => $u['email']],
            [
                'name'              => $u['name'],
                'password'          => $u['password'],
                'role'              => $u['role'],
                'email_verified_at' => now(), // seeded users skip verification
            ],
        ));
    }
}
```

> **Tip** — The full version in a real project also seeds related records per user (phones, profiles) with `firstOrCreate`, and has an optional commented-out `exportCredentialsToCSV()` that writes a Chrome-importable `url,username,password` CSV to `.docs/.private/` — local only, never committed.

**Reading reference data from a file (trait pattern):**

```php
trait HasRecordsFromCsv
{
    private function records(string $file): array
    {
        $csv = \League\Csv\Reader::createFromPath(database_path("data/{$file}"), 'r');
        $csv->setHeaderOffset(0);

        return iterator_to_array($csv->getRecords());
    }
}
```

> **Tip** — For large imports, wrap each row in `try/catch`, skip-and-count bad rows, and drive a `$this->command->getOutput()->createProgressBar()` so a long seed shows progress instead of silence.

**`DatabaseSeeder` — order reference data before dependents:**

```php
public function run(): void
{
    $this->call([
        // 1. Reference / lookup data first
        SettingsSeeder::class,
        // 2. Users
        UserSeeder::class,
        // 3. Transactional data that depends on the above
        // PostSeeder::class,
    ]);
}
```

## Next

- [DTOs & TypeScript](03-dtos-and-typescript.md)
