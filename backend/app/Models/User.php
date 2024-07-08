<?php

namespace App\Models;

use App\Http\Enums\PaymentStatus;
use App\Http\Enums\UserRoleType;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Permissions;
use App\Traits\Filterable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, Filterable;

    protected $table = 'users';

    protected $guarded = [];

    protected $appends = ['parent'];

    public $filterFields  = ['users.email', 'users.mobile', 'users.status', 'users.full_name'];
    public $filterKeywords = ['users.email', 'users.mobile', 'users.full_name'];
    private $joinRole = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $text_fields = ['full_name', 'email', 'mobile'];
    protected $casts = [
        'config' => 'array'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * parent
     */
    public function parent()
    {
        return $this->belongsTo(\App\Models\User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(\App\Models\User::class, 'parent_id');
    }
    public function role()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }
    /**
     * get parent
     */
    public function getParentAttribute()
    {
        if ($this->parent_id) {
            $parent = \App\Models\User::where('id', $this->parent_id)->first();

            if ($parent) {
                unset($parent->config);
                return $parent;
            }
        }

        return null;
    }

    /**
     * get role
     */
    public function getRole()
    {
        return Role::join('user_roles', 'user_roles.role_id', '=', 'roles.id')->where('user_roles.user_id', $this->id)->get();
    }

    /**
     * Get permissions
     */
    public function getPermissionsAttribute($value)
    {
        $permissions = Permissions::whereIn('id', explode(',', $value))->get();

        $results = [];
        foreach ($permissions as $permission) {
            array_push($results, $permission->name);
        }

        return $results;
    }

    /**
     * Set permissions
     */
    public function setPermissionsAttribute($value)
    {
        $this->attributes['permissions'] = implode(',', $value);
    }



    public function scopeIsAdmin($query)
    {
        return $query->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('roles.type', UserRoleType::ADMIN['value']);
    }
    public function scopeIsNotAdmin($query)
    {
        return $query->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('roles.type', '!=', UserRoleType::ADMIN['value']);
    }

    public function scopeIsStatus($query)
    {
        return $query->where('status', 1);
    }

    public function scopeJoinRole($query)
    {
        if($this->joinRole){
            return $query;
        }
        $query->leftJoin('user_roles as r', function ($join) {
            $join->on('users.id', '=', 'r.user_id');
        });
        $query->select('users.*');
        $query->groupBy('users.id');
        $this->joinRole = true;
		return $query;
    }

    public function scopeGetByRole($query, $role_id) {
        $query->joinRole();
        return $query->where('r.role_id', $role_id);
    }


    public function country() {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }
    public function updateAmount($money = 0) {
        $this->amount = (float) $this->amount + (float) $money;
        return $this;
    }
    public function updateTotalPaid($money = 0) {
        if ($this->total_paid == 0) {
            $listTransaction = PaymentTransaction::select('total')->getByUser($this->id)->isStatusApproved()->isBuy()->get();
            $this->total_paid = $listTransaction->sum('total');
        } else {
            $this->total_paid = (float) $this->total_paid + (float) $money;
        }
        return $this;
    }

    public function updateTotalPurchased($money = 0) {
        if ($this->total_purchased == 0) {
            $listTransaction = PaymentTransaction::select('total')->getByUser($this->id)->isStatusApproved()->isPurchase()->get();
            $this->total_purchased = $listTransaction->sum('total');
        } else {
            $this->total_purchased = (float) $this->total_purchased + (float) $money;
        }
        $this;
    }
    
    public function scopeGetByDay($query, $day) {
        return $query->whereDay('users.created_at', $day);
    }
}
