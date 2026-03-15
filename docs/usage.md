# Usage

To create migrations for all the tables, run:

```bash
php artisan migrate:generate
```

It will first ask if you want to log the migrations in the migrations table.

```bash
Using connection: mysql

Generating migrations for: comments,users

 Do you want to log these migrations in the migrations table? (yes/no) [yes]:
```

If you choose `yes`, it will prompt another question to confirm the batch number:

```bash
 Next Batch Number is: 1. We recommend using Batch Number 0 so that it becomes the "first" migration. [Default: 0] [0]:
```

Enter your desired batch number or press `Enter` to use the default value.

The tables from your schema:

```sql
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) COMMENT 'comment',
    `email` VARCHAR(255) UNIQUE,
    `email_verified_at` TIMESTAMP NULL,
    `password` VARCHAR(255),
    `remember_token` VARCHAR(100),
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE `comments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED,
    `title` VARCHAR(255),
    `comment` VARCHAR(255) UNIQUE,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `comments_user_id_index` (`user_id`),
    CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);
```

Will generate the following migration files.

Please note that the migration file for adding foreign keys is always generated last. This is because the tables referenced by the foreign keys need to exist before the foreign keys can be created.

* `2000_10_08_145641_create_comments_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('comment');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

* `2000_10_08_145641_create_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('title');
            $table->string('comment')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
```

* `2000_10_08_145644_add_foreign_keys_to_comments_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign('comments_user_id_foreign');
        });
    }
};
```
