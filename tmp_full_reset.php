<?php
// tmp_full_reset.php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Drop Columns
if (Schema::hasColumn('workspaces', 'plan_id')) {
    Schema::table('workspaces', function (Blueprint $table) {
        $table->dropColumn('plan_id');
    });
    echo "Dropped plan_id from workspaces\n";
}
if (Schema::hasColumn('users', 'is_active')) {
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('is_active');
    });
    echo "Dropped is_active from users\n";
}

// Drop table
Schema::dropIfExists('plans');
echo "Dropped plans table\n";

// Remove migration entries
DB::table('migrations')
    ->where('migration', 'LIKE', '2026_04_01_1716%')
    ->delete();
echo "Removed migration entries from 'migrations' table\n";
