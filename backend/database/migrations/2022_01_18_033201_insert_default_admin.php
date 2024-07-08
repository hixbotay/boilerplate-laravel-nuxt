<?php

use App\Http\Enums\UserRoleType;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Carbon;

class InsertDefaultAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $user = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@test.com',
            'mobile' => '0987654321',
            'country_id' => 1,
            'password' => Hash::make('Koph4iem132'),
            'email_verified_at' => Carbon::now()
        ]);
        foreach(UserRoleType::getAll() as $role){
            Role::create(
                [
                    'type' => $role['value'],
                    'name' => $role['display']
                ]
            );
        }
        

        $admin_role = Role::where('type', UserRoleType::ADMIN['value'])->first();

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $admin_role->id
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $user = User::where('email', 'admin@test.com')->first();
        UserRole::where('user_id', $user->id)->delete();
        $user->delete();
    }
}
