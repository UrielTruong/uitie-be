UITie Backend — Developer Guide
Tech Stack
Layer	Tech
Language	PHP 8.4
Framework	Laravel 13
Database	Azure SQL / Microsoft SQL Server
Architecture	Repository Pattern
Folder Structure
uitie-be/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/    ← API controllers (one per resource)
│   │   ├── Requests/           ← Form validation (authorize + rules)
│   │   └── Resources/          ← JSON response shaping (Resource + Collection)
│   ├── Models/                 ← Eloquent models (constants, fillable, casts, scopes)
│   ├── Providers/
│   │   └── AppServiceProvider.php  ← Bind interfaces → implementations here
│   └── Repositories/
│       ├── Contracts/          ← Interfaces (define what a repository must do)
│       └── *Repository.php     ← Implementations (actual DB queries)
│
├── bootstrap/
│   └── app.php                 ← App boot config (middleware, routing, exceptions)
│
├── config/                     ← App configs (database, auth, mail, cache…)
│
├── database/
│   ├── factories/              ← Fake data for testing
│   ├── migrations/             ← Schema definitions (run in order by timestamp)
│   └── seeders/                ← Seed DB with initial/test data
│
├── public/
│   └── index.php               ← Web entry point (NEVER edit directly)
│
├── routes/
│   ├── api.php                 ← All API routes (prefix: /api)
│   └── web.php                 ← Web routes (unused for pure API project)
│
├── storage/                    ← Logs, cache, uploaded files (auto-generated)
├── tests/                      ← Feature & Unit tests
│
├── .env                        ← Local environment config (NOT committed)
├── .env.example                ← Template for .env
└── composer.json               ← PHP dependencies
Architecture: Repository Pattern
Request → Controller → Repository Interface → Repository Impl → Model → DB
                  ↑                                              ↓
              Requests/                                      Resources/
           (validation)                                  (JSON response)
Rule: Controllers NEVER call Model::query() directly. Always go through a repository.

Adding a new resource requires these files: 1. app/Models/Foo.php 2. database/migrations/xxxx_create_foos_table.php 3. app/Repositories/Contracts/FooRepositoryInterface.php 4. app/Repositories/FooRepository.php 5. app/Providers/AppServiceProvider.php — add binding 6. app/Http/Requests/CreateFooRequest.php (+ update/list as needed) 7. app/Http/Resources/FooResource.php + FooCollection.php 8. app/Http/Controllers/Api/FooController.php 9. routes/api.php — add route

Prerequisites
Platform note: Steps below are for macOS Apple Silicon (M1/M2/M3/M4). Paths use /opt/homebrew — the default Homebrew prefix on M-chip Macs.

1. Install PHP 8.4
brew install php@8.4
echo 'export PATH="/opt/homebrew/opt/php@8.4/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
php -v   # should print PHP 8.4.x
2. Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer -V   # should print Composer version 2.x
3. Install MSSQL ODBC Driver + PHP Extensions (Mac M-chip)
This enables PHP to connect to Azure SQL / SQL Server.

Step 3a — Install build tools and ODBC driver:

brew install autoconf automake libtool

brew tap microsoft/mssql-release https://github.com/Microsoft/homebrew-mssql-release
brew install msodbcsql18 mssql-tools18
Accept the license prompt when asked.

Step 3b — Install PHP extensions via PECL:

sudo CXXFLAGS="-I/opt/homebrew/opt/unixodbc/include/" \
     LDFLAGS="-L/opt/homebrew/lib/" \
     pecl install sqlsrv

sudo CXXFLAGS="-I/opt/homebrew/opt/unixodbc/include/" \
     LDFLAGS="-L/opt/homebrew/lib/" \
     pecl install pdo_sqlsrv
Step 3c — Verify extensions loaded:

php -m | grep sqlsrv
# should show:
# pdo_sqlsrv
# sqlsrv
If extensions not shown, find your php.ini and add manually:

php --ini   # prints php.ini path
Add these two lines to php.ini:

extension=sqlsrv.so
extension=pdo_sqlsrv.so
Reference: https://learn.microsoft.com/en-us/sql/connect/php/installation-tutorial-linux-mac?view=sql-server-ver17

Project Setup (Step by Step)
Step 1 — Clone & install dependencies
git clone <repo-url> uitie-be
cd uitie-be
composer install
Step 2 — Configure environment
Paste the .env file provided by the team lead directly into the project root. Do NOT use .env.example — the real .env contains Azure SQL credentials.

# Confirm .env exists in project root
ls .env

# Generate app encryption key (run once)
php artisan key:generate
.env is in .gitignore — it is NEVER committed. Get it from the team lead, not from git.

The DB section of .env should look like:

DB_CONNECTION=sqlsrv
DB_HOST=<azure-sql-server>.database.windows.net
DB_PORT=1433
DB_DATABASE=uitie_db
DB_USERNAME=<username>
DB_PASSWORD=<password>
Step 3 — Run migrations
php artisan migrate
To reset and re-run all migrations from scratch:

php artisan migrate:fresh
Step 4 — Run the server
php artisan serve
# API available at http://127.0.0.1:8000/api/v1
Step 5 — Test with Postman or Bruno
Download: - Postman: https://www.postman.com/downloads/ - Bruno (lighter, recommended): https://www.usebruno.com/

List all API endpoints:

php artisan route:list --path=api
Current endpoints to test:

Method	URL	Description
GET	http://127.0.0.1:8000/api/v1/users	List users (paginated)
POST	http://127.0.0.1:8000/api/v1/users	Create new user
POST /api/v1/users — request body (JSON):

{
  "name": "Nguyen Van A",
  "email": "vana@example.com",
  "password": "secret123"
}
GET /api/v1/users — optional query param:

?per_page=10
Common Artisan Commands
Generate files
# Model only
php artisan make:model Post

# Model + migration
php artisan make:model Post -m

# Model + migration + controller (API resource)
php artisan make:model Post -m --api -c

# Controller only (inside Api subfolder)
php artisan make:controller Api/PostController --api

# Form Request
php artisan make:request CreatePostRequest

# API Resource
php artisan make:resource PostResource

# API Resource Collection
php artisan make:resource PostCollection --collection

# Migration only
php artisan make:migration create_posts_table
php artisan make:migration add_status_to_posts_table
Database
# Run pending migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Rollback all and re-run
php artisan migrate:fresh

# Rollback all, re-run, then seed
php artisan migrate:fresh --seed

# Run seeders only
php artisan db:seed
Debugging
# List all registered routes
php artisan route:list

# Open interactive PHP shell (with app context)
php artisan tinker

# Clear all caches
php artisan optimize:clear

# View app logs (tail)
php artisan pail
Adding a New Feature (Example: Posts)
1. Generate files
php artisan make:model Post -m
php artisan make:controller Api/PostController --api
php artisan make:request CreatePostRequest
php artisan make:resource PostResource
php artisan make:resource PostCollection --collection
2. Write migration (database/migrations/xxxx_create_posts_table.php)
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->text('body')->nullable();
    $table->timestamps();
});
3. Create interface (app/Repositories/Contracts/PostRepositoryInterface.php)
namespace App\Repositories\Contracts;

interface PostRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): Post;
}
4. Create repository (app/Repositories/PostRepository.php)
namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;

class PostRepository implements PostRepositoryInterface
{
    public function __construct(private readonly Post $model) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }

    public function create(array $data): Post
    {
        return $this->model->create($data);
    }
}
5. Bind in AppServiceProvider.php
$this->app->bind(PostRepositoryInterface::class, PostRepository::class);
6. Add route in routes/api.php
Route::prefix('v1')->group(function () {
    Route::apiResource('posts', PostController::class);
});
7. Run migration
php artisan migrate
Environment Variables Reference
Key	Description	Example
APP_ENV	local / production	local
APP_KEY	Encryption key (auto-generated)	—
APP_DEBUG	Show errors in response	true (local only)
DB_CONNECTION	Database driver	sqlsrv
DB_HOST	DB server host	127.0.0.1
DB_PORT	DB port	1433
DB_DATABASE	Database name	uitie_db
DB_USERNAME	DB user	sa
DB_PASSWORD	DB password	—
QUEUE_CONNECTION	Queue driver	database
MAIL_MAILER	Mail driver	log (dev), smtp (prod)
Known Issues & Decisions
See progress.md for full details. Key points:

MSSQL cascade conflicts — Some FK relations use NO ACTION instead of CASCADE due to MSSQL's multiple cascade path restriction. App must clean up related records before deleting a user.

notifications table — Conflicts with Laravel's built-in Notifiable trait. Either remove Notifiable from User model or rename the table to user_notifications.

reports.resolved_by — SET NULL on admin delete conflicts with a CHECK constraint. Use NO ACTION on that FK or remove the NOT NULL check.

User roles/statuses — Managed via constants in User model (User::ROLE_STUDENT, User::STATUS_ACTIVE, etc.). Do not hardcode strings.
