# Roles & Permissions

## 1. Overview

This app has three kinds of people: **platform admins**, **shop staff** (people who work for one vendor shop) and **customers**. Each needs different abilities, and shop staff must never see another shop's data. We solve this with [Spatie Laravel-Permission](https://spatie.be/docs/laravel-permission) and one core idea: **a role says what you can *do*; your `shop_id` says *whose data* you can do it to.** The two are kept apart on purpose. A role never grants access to a shop, and belonging to a shop never grants an ability.

## 2. How it works

### The two role pools

Spatie keys every role and permission by a **guard name**. We use that to run two completely separate pools that can't be mixed up:

| | Admin pool | Shop pool |
|---|---|---|
| Who | Platform staff | Staff of one vendor shop |
| Table / model | `admins` / `App\Models\Admin` | `shop_users` / `App\Models\ShopUser` |
| Guard (`guard_name`) | `admin` | `shop_user` |
| Seeded roles | `super-admin`, `shop-manager`, `operator`, `finance`, `delivery-manager` | `shop-owner`, `shop-staff`, `shop-salesman` |
| Scope of a role | Whole platform | Only the user's own `shop_id` |
| Login / route middleware | `admin` | `shop_user` + `shop.context` |

Customers (`users` table, `web` guard) have no roles. Spatie refuses to cross pools: `$shopUser->assignRole('super-admin')` throws, because that role lives under the `admin` guard. The same role *name* may exist in both pools without clashing, since the unique key is `(name, guard_name)`.

Both models declare their pool:

```php
// app/Models/Admin.php
use HasFactory, Notifiable, HasRoles;
protected $guard_name = 'admin';

// app/Models/ShopUser.php
use HasFactory, Notifiable, HasRoles;
protected $guard_name = 'shop_user';
```

> The old `admins.role` column (the `RoleTypeState` enum) still exists but is **not used for authorization**. Spatie's tables are the source of truth.

### Why permissions alone aren't enough for data isolation

A permission answers *"can this person edit products?"* It says nothing about *which* products. If two shops both have a `shop-owner`, both hold `product-edit`, and a permission check alone would let either edit any product. So every shop-side query is **also** scoped by `shop_id`. Both checks must pass:

1. **Permission**: may they perform this action at all? (`authorizeUserCheck`)
2. **Scope**: is this row theirs? (`forCurrentShop()`)

Scoping lives in one trait, `app/Models/Concerns/BelongsToShop.php`:

```php
public function scopeForCurrentShop(Builder $query): Builder
{
    $shopId = currentShopId();

    return $shopId ? $this->scopeForShop($query, $shopId) : $query->whereRaw('1 = 0');
}
```

It **fails closed**: with no logged-in shop user it matches nothing rather than everything. It is opt-in per query, not a global scope, because a shop user is also allowed to browse the public storefront as a shopper, and a global scope would wrongly hide other shops' products there. The trait is on `Product`, `VendorOrder`, `Coupon` and `VendorPayout`.

The `shop.context` middleware (`SetShopContext`) runs on the whole `/shop-user` route group. It guarantees the logged-in shop user actually has a `shop_id` and logs them out if not.

### How a new shop gets its first shop-owner

The `ShopUser` model watches its own `created` event (`ShopUser::booted()`). If the new user is the **first** one for that `shop_id`, it is given the owner role automatically:

```php
static::created(function (ShopUser $user): void {
    $hasOthers = static::where('shop_id', $user->shop_id)->where('id', '!=', $user->id)->exists();
    if ($hasOthers) { return; }

    $role = Role::where('name', config('access.default_shop_owner_role'))
        ->where('guard_name', config('access.guards.shop_user'))
        ->first();

    $role ? $user->assignRole($role) : Log::warning('Owner role missing; run RolesAndPermissionsSeeder.', [...]);
});
```

Because this is on the model, it works for **every** way a shop can be created: the admin *Shops* screen, and approving a request in *Shop Registrations*. Neither screen needed changing.

## 3. Why it's built this way

**No hardcoded role checks.** Code asks *"can they do X?"*, never *"are they an admin?"*. Roles are just named bundles of permissions stored in the database. If `operator` is later allowed to `order-refund`, you attach that permission to the role and no code changes. The only role name in configuration is `default_shop_owner_role` in `config/access.php`, used to *give* a role at shop creation, never to *check* one.

**Adding a new role needs zero code changes.** A new role is a database row plus links to existing permissions. `authorizeUserCheck('some-permission')` already works for any role holding that permission. Code changes are needed only when you add a genuinely **new capability**, because someone has to write the `authorizeUserCheck('new-permission')` call that protects it.

**Two shops can safely share role names.** A shop role is only a set of abilities. It carries no shop identity, so `shop-staff` in shop A and `shop-staff` in shop B are the same role. Isolation comes entirely from `shop_id` scoping (see above), not from the role. Two staff with the identical role still can't see each other's data.

**Guards keep the pools separate for free.** We didn't invent our own separation. Spatie already enforces that a model can only receive roles from its own guard.

## 4. How to use it

### Create a new admin role and give it permissions

Roles and permissions are plain rows. Use `tinker`, a seeder, or an admin screen:

```php
use Spatie\Permission\Models\{Role, Permission};

$permission = Permission::firstOrCreate(['name' => 'order-refund', 'guard_name' => 'admin']);

$role = Role::firstOrCreate(['name' => 'support-lead', 'guard_name' => 'admin']);
$role->givePermissionTo(['order-view', 'order-refund']);   // or ->syncPermissions([...])
```

To make it permanent for fresh installs, add it to `ADMIN_ROLES` in `database/seeders/RolesAndPermissionsSeeder.php`. The seeder is idempotent, so you can re-run it safely with `php artisan db:seed --class=RolesAndPermissionsSeeder`.

### Create a new shop role and give it permissions

Same thing, with the `shop_user` guard:

```php
$role = Role::firstOrCreate(['name' => 'shop-inventory', 'guard_name' => 'shop_user']);
$role->givePermissionTo('product-view');
```

It is immediately assignable to staff (see the role-change example below).

### Check a permission in a controller or route

The helper is `authorizeUserCheck()` in `app/Helpers/authorization.php`. It never looks at role names:

```php
authorizeUserCheck('shop-approve', 'admin');        // pin to the admin pool
authorizeUserCheck('product-edit', 'shop_user');    // pin to the shop pool
authorizeUserCheck('order-view');                   // any configured guard that's logged in and has it
```

In a controller or Livewire action:

```php
abort_unless(authorizeUserCheck('shop-delete', 'admin'), 403);
```

On a route, use the `authorize:<permission>,<guard>` middleware. Put it **after** the authentication middleware:

```php
Route::middleware('admin')->group(function () {
    Route::get('/shop-registrations', ShopRegistrations::class)
        ->middleware('authorize:shop-view,admin');
});

Route::patch('/staff/{id}/role', [ShopStaffController::class, 'updateRole'])
    ->middleware('authorize:staff-assign-role,shop_user');
```

> Livewire actions are separate HTTP requests, so hiding a button is not enough. `ShopRegistrations` re-checks the permission inside every action (`authorizeAction()`).

**Admin vs shop user.** They are different guards and different tables, so always pass the guard when a route belongs to one pool. Without a guard, the helper accepts whichever configured guard is logged in and holds the permission.

### Check a permission in a Blade view

Use the `@authorizeUser` directive (registered in `AppServiceProvider`). The guard is optional:

```blade
@authorizeUser('shop-approve', 'admin')
    <x-button label="Approve" wire:click="confirmApprove({{ $r->id }})" />
@endauthorizeUser
```

Plain `@can` only checks the *default* guard, which is why we use our own directive for the `admin` and `shop_user` guards.

### Assign a role to a new admin user

```php
$admin = Admin::create([...]);
$admin->assignRole('finance');          // one role
$admin->syncRoles(['finance', 'operator']); // replace with exactly these
```

`AdminSeeder` does this for the default admin: `$admin->syncRoles('super-admin')`. Run `RolesAndPermissionsSeeder` **before** it, since the role must already exist.

### Assign or change a role for a shop_user

A shop's first user becomes owner automatically. To change anyone else's role, go through `ShopStaffRoleService` (`app/Services/ShopStaffRoleService.php`), which is what `ShopStaffController@updateRole` calls (`PATCH /shop-user/staff/{id}/role`, field `role`):

```php
$service->changeRole($request->user('shop_user'), $target, 'shop-salesman');
```

It enforces the rules for you:

- The actor must hold `staff-assign-role`, checked by permission and not by role name.
- The target must be in the **same shop** (otherwise 403).
- The role must exist in the **shop** pool, so an admin role name is rejected as unknown.
- **No privilege escalation**: you can't hand out a role with permissions you don't have yourself.
- A shop must always keep at least one user who can assign roles. The change is rolled back if it would leave none.

For a plain one-off (seeder, tinker) you can skip the service: `$shopUser->syncRoles('shop-staff')`.

### Scope a query so a shop_user only sees their own shop's data

```php
// app/Livewire/Vendor/Product/ProductList.php
Product::forCurrentShop()->with('category', 'images')->latest()->paginate(20);

// look up a single record: someone else's id simply 404s
Product::forCurrentShop()->findOrFail($this->productIds)->delete();
```

For a staff list, `ShopStaffController` does the same by hand: `ShopUser::where('shop_id', currentShopId())->findOrFail($id)`.

To add scoping to another model, add `use BelongsToShop;` (the table needs a `shop_id` column).

## 5. Common mistakes to avoid

**1. Hardcoding role names.**

```php
// Wrong: breaks the moment a new role should have this power
if ($user->hasRole('shop-owner')) { ... }

// Right: ask about the ability
if (authorizeUserCheck('staff-assign-role', 'shop_user')) { ... }
```

**2. Forgetting `shop_id` scoping because the permission passed.** A passing permission check means *"they may edit products"*, not *"they may edit **this** product"*.

```php
// Wrong: any shop's product, as long as the user has product-delete
Product::find($id)->delete();

// Right
Product::forCurrentShop()->findOrFail($id)->delete();
```

This was a real bug in `ProductList::deleteProduct()`, which used `Product::find($this->productIds)`. Since `$productIds` is a public Livewire property, a shop user could have deleted another shop's product. It's fixed now. Also never trust a shop id sent from the browser. Take it from `currentShopId()`.

**3. Mixing pools.**
- Don't assign an admin role to a `shop_user`, or a shop role to an `admin`. Spatie will throw, which is intentional.
- Don't create a shop role with `guard_name = 'admin'` (or the reverse) to "share" it. It would attach to the wrong table.
- Every new role or permission needs the right `guard_name`, or the check silently returns `false`.

**4. Forgetting to run the seeder, or the guard when you call the helper.**
- On a new environment run `php artisan migrate` then `php artisan db:seed`. If the owner role is missing, the first shop user is created **without** a role, and a warning is logged.
- Permission lookups are cached by Spatie. After editing roles by hand in the database, run `php artisan permission:cache-reset`. The helpers above (`assignRole`, `givePermissionTo`, ...) clear it for you.

**5. Removing the last role manager.** Don't remove `staff-assign-role` from the only role that has it in a shop, or nobody can manage staff. `ShopStaffRoleService` guards the normal path, but direct database edits do not.

## What protects what

Every admin and vendor page is guarded by a route middleware, and every state-changing Livewire action re-checks its own permission (Livewire actions are separate requests that skip route middleware). Use `authorizeAdmin('perm')` / `authorizeShop('perm')` from `App\Livewire\Concerns\AuthorizesPermissions` inside actions.

`authorize:a|b,guard` means **any one** of the permissions is enough.

**Admin pool (`admin` guard)**

| Page | Permission to open | Actions inside |
|---|---|---|
| Dashboard, Settings | any logged-in admin | own profile |
| Products | `product-view` | |
| Categories | `category-manage` | create / edit / delete |
| Orders, Order detail | `order-view` or `delivery-view` | receive, ship: `order-process`; out for delivery, delivered: `delivery-update-status` |
| Invoices | `order-view` | |
| Payouts | `payout-view` | approve / reject: `payment-process` |
| Shops | `shop-view` | create: `shop-create`; edit: `shop-edit`; delete: `shop-delete` |
| Shop Registrations | `shop-view` | approve / reject: `shop-approve`; delete: `shop-delete` |
| Coupons | `coupon-manage` | all actions |
| Messages | `message-view` | mark read / unread |
| Store policies | `policy-manage` | save |

`product-view`, `category-manage`, `coupon-manage`, `message-view` and `policy-manage` are not part of the five named admin roles. Only `super-admin` holds them by default. Attach them to other roles from the database when needed.

**Shop pool (`shop_user` guard)**

| Page | Permission to open | Actions inside |
|---|---|---|
| Dashboard, Settings | any logged-in shop user | own profile; the AI auto-reply toggle needs `shop-edit-settings` |
| Products, Reviews | `product-view` | create: `product-create`; edit: `product-edit`; delete: `product-delete` |
| Categories | `category-manage` | create / edit / delete (only categories your own shop owns) |
| Orders, Order detail, Invoice | `order-view` | receive / cancel / pending: `order-update-status` |
| Earnings | `earnings-view` | request payout |
| Coupons | `coupon-manage` | all actions |
| Chat | `chat-reply` | send / read |
| Staff role change | `staff-assign-role` | `PATCH /shop-user/staff/{id}/role` |

Permission checks answer *"may they do this?"*. Every shop-side query is **also** scoped to the user's shop (`forCurrentShop()`), because both questions must be answered. Two cross-shop holes were found and fixed while adding these gates: vendor order detail could open any shop's order, and vendor category edit/delete could touch any category.

## Existing accounts (backfill)

Migration `..._seed_roles_and_backfill_existing_accounts.php` runs `RolesAndPermissionsSeeder`, then gives every admin that has no role the `super-admin` role (before roles existed, every admin was a "Super Admin"). The seeder also makes each existing shop's first shop user its owner. Without this, protecting the pages would lock everyone out.

## Setup checklist

```bash
composer install            # picks up spatie/laravel-permission and the autoloaded helper file
php artisan migrate         # permission tables, roles seeded, existing accounts backfilled
php artisan db:seed         # (fresh installs) default super-admin; safe to re-run
```

New permissions added to the seeder later need `php artisan db:seed --class=RolesAndPermissionsSeeder`. Super-admin picks up new admin permissions automatically when it runs.

## File map

| File | Purpose |
|---|---|
| `config/permission.php` | Spatie config (teams off; pools are separated by guard) |
| `config/access.php` | Guard names and the default owner role name |
| `database/migrations/..._create_permission_tables.php` | Spatie's tables |
| `database/migrations/..._add_shop_id_to_shop_scoped_tables.php` | Adds a nullable `shop_id` where one is missing |
| `database/seeders/RolesAndPermissionsSeeder.php` | Seeds both pools; backfills owners for existing shops |
| `app/Helpers/authorization.php` | `authorizeUserCheck()`, `currentShopId()` |
| `app/Http/Middleware/AuthorizePermission.php` | `authorize:<perm>[|<perm>],<guard>` route middleware |
| `app/Livewire/Concerns/AuthorizesPermissions.php` | `authorizeAdmin()` / `authorizeShop()` for Livewire actions |
| `database/migrations/..._seed_roles_and_backfill_existing_accounts.php` | Seeds roles and backfills existing admins / shop owners |
| `app/Http/Middleware/SetShopContext.php` | `shop.context`: every shop request must have a shop |
| `app/Models/Concerns/BelongsToShop.php` | `forShop()` / `forCurrentShop()` scopes |
| `app/Services/ShopStaffRoleService.php` | Safe role changes inside a shop |
| `app/Http/Controllers/ShopStaffController.php` | Example controller using the service |
