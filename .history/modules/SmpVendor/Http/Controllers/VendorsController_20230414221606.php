<?php

/**
 * NexoPOS Controller
 *
 * @since  1.0
 **/

namespace Modules\SmpVendor\Http\Controllers;

use Modules\SmpVendor\Crud\VendorCrud;
use App\Http\Controllers\DashboardController;
use Modules\SmpVendor\Models\Vendor;
use Modules\SmpVendor\Services\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class VendorsController extends DashboardController
{
    /**
     * @param Vendors
     */
    protected $usersService;

    public function __construct()
    {
        parent::__construct();
    }

    public function listVendors()
    {
        return $this->view('pages.dashboard.crud.table', [
            'title' => __('Users List'),
            'createUrl' => url('/dashboard/users/create'),
            'description' => __('Manage all users available.'),
            'src' => url('/api/nexopos/v4/crud/ns.users'),
        ]);
    }

    public function createVendor()
    {
        ns()->restrict([ 'create.users' ]);

        return UserCrud::form();
    }

    public function editVendor(User $user)
    {
        ns()->restrict([ 'update.users' ]);

        if ($user->id === Auth::id()) {
            return redirect(ns()->route('ns.dashboard.users.profile'));
        }

        return UserCrud::form($user);
    }

    public function getUsers(User $user)
    {
        ns()->restrict([ 'read.users' ]);

        return User::get([ 'username', 'id', 'email' ]);
    }

    /**
     * displays the permission manager UI
     *
     * @return View
     */
    public function permissionManager()
    {
        /**
         * force permissions check
         */
        ns()->restrict([ 'update.roles' ]);

        return $this->view('pages.dashboard.users.permission-manager', [
            'title' => __('Permission Manager'),
            'description' => __('Manage all permissions and roles'),
        ]);
    }

    /**
     * displays the user profile
     *
     * @return view
     */
    public function getProfile()
    {
        ns()->restrict([ 'manage.profile' ]);

        return $this->view('pages.dashboard.users.profile', [
            'title' => __('My Profile'),
            'description' => __('Change your personal settings'),
            'src' => url('/api/nexopos/v4/forms/ns.user-profile'),
            'submitUrl' => url('/api/nexopos/v4/users/profile'),
        ]);
    }

    /**
     * returns a list of existing roles
     *
     * @return array roles with permissions
     */
    public function getRoles()
    {
        return Role::with('permissions')->get();
    }

    /**
     * Returns a list of permissions
     *
     * @return array permissions
     */
    public function getPermissions()
    {
        return Permission::get();
    }

    /**
     * update roles permissions
     *
     * @param Request $request
     * @return Json
     */
    public function updateRole(Request $request)
    {
        ns()->restrict([ 'update.roles' ]);

        $roles = $request->all();

        foreach ($roles as $roleNamespace => $permissions) {
            $role = Role::namespace($roleNamespace);

            if ($role instanceof Role) {
                $removedPermissions = collect($permissions)->filter(fn ($permission) => ! $permission);
                $grantedPermissions = collect($permissions)->filter(fn ($permission) => $permission);

                $role->removePermissions($removedPermissions->keys());
                $role->addPermissions($grantedPermissions->keys());
            }
        }

        return [
            'status' => 'success',
            'message' => __('The permissions has been updated.'),
        ];
    }

    /**
     * List all available roles
     *
     * @return View
     */
    public function rolesList()
    {
        ns()->restrict([ 'read.roles' ]);

        return RolesCrud::table();
    }

    /**
     * List all available roles
     *
     * @return View
     */
    public function editRole(Role $role)
    {
        ns()->restrict([ 'update.roles' ]);

        return RolesCrud::form($role);
    }

    public function createRole(Role $role)
    {
        return RolesCrud::form();
    }

    public function cloneRole(Role $role)
    {
        ns()->restrict([ 'create.roles' ]);

        $this->usersService = app()->make(Users::class);

        return $this->usersService->cloneRole($role);
    }
}
