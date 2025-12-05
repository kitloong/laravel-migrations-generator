# Squash Migrations

```bash
--squash
```

The `--squash` option combines all generated migrations into a single file.

### Example

```bash
php artisan migrate:generate --squash
```

This command will generate a single migration file containing all the migrations.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            ...
        });
        
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            ...
        });
        
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
        
        Schema::dropIfExists('users');
        
        Schema::dropIfExists('comments');
    }
};
```
