<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $users = [
            'superadmin@pusakahukum.test' => ['email' => 'superadmin@sipakem.test', 'name' => 'Super Admin SIPAKEM'],
            'admin@pusakahukum.test' => ['email' => 'admin@sipakem.test', 'name' => 'Admin Pengelola'],
            'internal@pusakahukum.test' => ['email' => 'internal@sipakem.test', 'name' => 'User Internal'],
        ];

        foreach ($users as $oldEmail => $replacement) {
            $query = DB::table('users')->where('email', $oldEmail);

            if (! $query->exists()) {
                continue;
            }

            $emailAlreadyExists = DB::table('users')
                ->where('email', $replacement['email'])
                ->exists();

            $query->update($emailAlreadyExists ? ['name' => $replacement['name']] : $replacement);
        }

        DB::table('users')
            ->where('name', 'Super Admin PUSAKA')
            ->update(['name' => 'Super Admin SIPAKEM']);
    }

    public function down(): void
    {
        $users = [
            'superadmin@sipakem.test' => ['email' => 'superadmin@pusakahukum.test', 'name' => 'Super Admin PUSAKA'],
            'admin@sipakem.test' => ['email' => 'admin@pusakahukum.test', 'name' => 'Admin Pengelola'],
            'internal@sipakem.test' => ['email' => 'internal@pusakahukum.test', 'name' => 'User Internal'],
        ];

        foreach ($users as $oldEmail => $replacement) {
            $query = DB::table('users')->where('email', $oldEmail);

            if (! $query->exists()) {
                continue;
            }

            $emailAlreadyExists = DB::table('users')
                ->where('email', $replacement['email'])
                ->exists();

            $query->update($emailAlreadyExists ? ['name' => $replacement['name']] : $replacement);
        }
    }
};
