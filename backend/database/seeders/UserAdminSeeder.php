<?php

namespace Database\Seeders;

use App\Http\Enums\UserRoleType;
use App\Models\MerchantOrder;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();
        UserRole::truncate();
        Role::truncate();
        DB::raw('truncate table user_roles');
        DB::raw('truncate table roles');
        $user = User::create([
            'full_name' => 'Admin',
            'email' => 'admin@test.com',
            'mobile' => '0987654321',
            'country_id' => 1,
            'password' => Hash::make('admin'),
            'email_verified_at' => Carbon::now()
        ]);
        $roles = [];
        foreach(UserRoleType::getAll() as $role){
            $roles[] = [
                    'type' => $role['value'],
                    'name' => $role['display']
            ];
        }
        Role::insert($roles);

        $admin_role = Role::where('type', UserRoleType::ADMIN['value'])->first();
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $admin_role->id
        ]);
    }
}
