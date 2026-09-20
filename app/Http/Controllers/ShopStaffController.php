<?php

namespace App\Http\Controllers;

use App\Models\ShopUser;
use App\Services\ShopStaffRoleService;
use Illuminate\Http\Request;

/** Example shop-side controller: promote/demote a staff member within your own shop. */
class ShopStaffController extends Controller
{
    public function updateRole(Request $request, int $id, ShopStaffRoleService $service)
    {
        $data = $request->validate(['role' => ['required', 'string']]);

        // Scoped lookup: a staff id from another shop simply 404s.
        $target = ShopUser::where('shop_id', currentShopId())->findOrFail($id);

        $service->changeRole($request->user('shop_user'), $target, $data['role']);

        return back()->with('success', "{$target->name}'s role was updated.");
    }
}
