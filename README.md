# Rijschool Vierkantewielen

A web application for **Rijschool Vierkantewielen**, a (fictional) Dutch driving school. It has a public website for prospective students and a back office where the driving school manages students, instructors, cars, lesson packages, driving lessons, invoices and payments. Instructors manage their own lesson schedule and students request their lessons online.

This is a school project, built with **Laravel 12**, **Blade**, **Tailwind CSS**, **Alpine.js** and **MySQL 8**.

## Features

**Public website**
- Landing page that presents the driving school and its driving lessons
- Registration, login and password reset (Laravel Breeze)
- Light and dark theme toggle

**Back office (admin only)**
- **Dashboard**: statistics, recent activity, quick actions and a maintenance mode switch
- **Students** (`/students`): create, view, edit and delete students
- **Instructors** (`/instructors`): manage instructors
- **Cars** (`/autos`): fleet overview (brand, model, license plate, electric or gasoline)
- **Packages** (`/packages`): lesson packages and price per lesson
- **Accounts** (`/accounts`): user accounts with contact details and roles
- **Invoices** (`/invoices`): create and edit invoices, mark them as paid or unpaid
- **Payments** (`/betalingen`): register payments against invoices
- **Registrations** (`/registrations`): link a student to a lesson package, with start and end date and lessons used / remaining
- **Lessons** (`/lessons`): plan, move, cancel and delete lessons with instructor, car and pick-up address; filter by period, status and instructor; handle lesson requests from students

**Instructor (`/instructeur/lessen`)**
- Personal schedule with upcoming lessons, the student's phone number and pick-up address
- Accept open lesson requests from students and choose a car
- Reschedule a lesson, mark it as completed or cancelled, and add a progress note for the student

**Student (`/leerling/lessen`)**
- Overview of their lesson packages with how many lessons are left
- Request a lesson (date, time, pick-up address, what to practise)
- Cancel a request, or a planned lesson up to 24 hours in advance
- Read the instructor's notes and leave a comment on completed lessons

Instructors and students also get a dashboard with their next lessons.

**Lesson rules**
- A lesson goes through the statuses *Aangevraagd* (requested by the student), *Gepland*, *Voltooid* or *Geannuleerd*
- An instructor, car or student can't be booked for two overlapping lessons
- A student can't plan more lessons than their package contains (cancelled lessons don't count) or outside the package period

Access is controlled by the `roles` table: the back office requires an active `Admin` role (`AdminMiddleware`); the instructor and student pages require an active `Instructeur` or `Leerling` role (`role:` middleware, `CheckRole`).

## Data model

| Table | Purpose |
| --- | --- |
| `users`, `contacts`, `roles` | Accounts. Login is by email, which lives in `contacts` |
| `students`, `instructors` | Students (relation number) and instructors, linked to a user |
| `packages`, `registrations` | Lesson packages and the student's registration for a package |
| `lessons`, `pick-up_addresses`, `driving_lessons_per_pickup_addresses` | Planned driving lessons and pick-up locations |
| `autos` | Cars used for lessons |
| `exams` | Exams per registration (passed / failed) |
| `invoices`, `payments` | Billing |
| `notifications` | Messages to students and instructors (sick, lesson change, ...) |
| `settings` | Application settings such as maintenance mode |

Accounts and invoices are read and written through **MySQL stored procedures** (`spGetAllAccounts`, `spAddInvoice`, ...), which are created by migrations. That is why the app needs MySQL and does not run on SQLite.

ERD diagrams are in `database/ERD/`.

## Running with Docker (recommended)

You only need Docker Desktop.

```bash
docker compose up -d --build
```

| Service | URL |
| --- | --- |
| App | http://localhost:8000 |
| Adminer (database UI) | http://localhost:8080 (server `mysql`, user `rijschool`, password `secret`) |
| MySQL | `localhost:3306` |

On the first start the container waits for MySQL, runs the migrations and seeds demo data. Settings for the local stack are in `.env.docker`.

**Demo accounts**

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | `Admin1234` |
| Instructor | `instructeur@example.com` | `Instructeur1234` |
| Student | `test@example.com` | `Test1234` |

The demo student has a 20-lesson package with completed, planned and cancelled lessons, plus one open lesson request that the demo instructor can accept.

Useful commands:

```bash
docker compose logs -f app                      # follow the app logs
docker compose exec app php artisan migrate     # run artisan commands
docker compose up -d --build app                # rebuild after code changes
docker compose down                             # stop (data is kept)
docker compose down -v                          # stop and delete all data
```

> The image contains the code, so rebuild it after changing PHP, Blade, CSS or JS files. For live editing, use the local setup below.

## Running locally without Docker

Requirements: PHP 8.2+, Composer, Node.js 20+ and MySQL 8 (you can run just the database with `docker compose up -d mysql`).

```bash
composer install
npm install
cp .env.example .env          # set DB_DATABASE=rijschool, DB_USERNAME=rijschool, DB_PASSWORD=secret
php artisan key:generate
php artisan migrate --seed
composer run dev              # starts php artisan serve, the queue worker and Vite
```

The app runs on http://localhost:8000.

## Deploying to CasaOS with Portainer

The production stack is in `docker-compose.prod.yml`: the app (PHP 8.3 + Apache) and MySQL 8, each with a persistent volume. The database port is not exposed.

1. Open Portainer on your CasaOS server and go to **Stacks → Add stack**.
2. Choose **Repository** and enter:
   - Repository URL: the URL of this Git repository (add credentials if it is private)
   - Repository reference: `refs/heads/main`
   - Compose path: `docker-compose.prod.yml`
3. Under **Environment variables** switch to **Advanced mode** and paste the contents of [`stack.env.example`](stack.env.example). At minimum change:
   - `DB_PASSWORD` and `DB_ROOT_PASSWORD`
   - `APP_URL`, the address you will open the app on (for example `http://192.168.1.50:8085`)
   - `APP_PORT` if `8085` is already taken (CasaOS itself uses port 80)
   - `SEED_DATABASE=true` if you want the demo data on the first start
4. Click **Deploy the stack**. Portainer clones the repository and builds the image on the server, which takes a few minutes the first time.
5. Open `http://<casaos-ip>:8085`.

**Updating:** push your changes to Git, then open the stack in Portainer and click **Pull and redeploy**. Migrations run automatically when the container starts. You can also enable **GitOps updates** in the stack settings to redeploy automatically.

**Good to know**
- `APP_KEY` is generated on the first start and stored in the `rijschool_app_storage` volume. If you set it yourself, never change it afterwards, or existing sessions and encrypted data stop working.
- The demo seed only runs once (a marker file is kept in the storage volume).
- Data lives in the Docker volumes `rijschool_db_data` and `rijschool_app_storage`. Back these up.
- The app trusts reverse proxy headers, so it works behind Nginx Proxy Manager, Traefik or a Cloudflare Tunnel for HTTPS. Set `APP_URL` to the public `https://` address in that case.
- To use a prebuilt image instead of building on the server, push one to a registry and set `APP_IMAGE`.

## Docker files

| File | Purpose |
| --- | --- |
| `Dockerfile` | Multi-stage build: Composer dependencies, Vite assets, then a PHP 8.3 Apache runtime |
| `docker/entrypoint.sh` | Waits for the database, sets up `APP_KEY`, runs migrations and the optional seed, caches config and routes |
| `docker/php/php.ini` | PHP and OPcache settings |
| `docker/mysql/my.cnf` | MySQL settings for the local stack |
| `docker-compose.yml` | Local stack: app, MySQL and Adminer |
| `docker-compose.prod.yml` | Production stack for CasaOS and Portainer |
| `.env.docker` | Environment for the local app container |
| `stack.env.example` | Environment variables template for Portainer |

## Tests

```bash
php artisan test
```

## License

MIT
