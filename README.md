
<h1>Arukereso Orders API - Test Homework</h1>

<h2>📃Documentation with sample JSON parameters</h2>

[Download](https://github.com/gergipeter/arukereso_test_gergipeter/blob/main/arukereso_order_api_technical_doc.pdf)

<h2>🖥 Screenshot:</h2>

![screenshot](https://github.com/gergipeter/arukereso_test_gergipeter/blob/main/screenshot.PNG)

<h2>🛠️ Installation Steps:</h2>

```bash
    git clone https://github.com/gergipeter/arukereso_test_gergipeter.git
    cd arukereso_test_gergipeter
```
<h5>Run Composer Update</h5>

```bash
    composer update
```
<h5>Set up your MySQL username and password in the .env file</h5>

```bash
    cp .env.example .env
```
<h5>Migrations, Tests, Seeders</h5>

```bash
    php artisan key:generate
    php artisan migrate
    php artisan test --testdox
    php artisan db:seed
```
<h5>Start Server</h5>

```bash
    php artisan serve
```


<h2>📖 Start Page - Swagger API DOC</h2>

    http://127.0.0.1:8000/api/doc

<h2>💻 Built with</h2>

Technologies used in the project:

-  **PHP** 8.3.1
-  **MySQL** 8.0.35
-  **Laravel** 10.40.0

