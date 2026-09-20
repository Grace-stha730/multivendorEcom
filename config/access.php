<?php

/*
| Non-authorization settings for the role/permission layer.
| Authorization decisions themselves never read role names; they only ask
| "does this user have permission X?" (see app/Helpers/authorization.php).
*/
return [

    // Guards that carry roles/permissions, in the order authorizeUserCheck() probes them.
    // Each guard is its own role pool (Spatie keys roles and permissions by guard_name).
    'guards' => [
        'admin' => 'admin',           // platform staff  -> admins table
        'shop_user' => 'shop_user',   // shop staff      -> shop_users table (scoped by shop_id)
    ],

    // Bootstrap default: the role given to the first shop_user created for a new shop.
    // This is only used to *assign* a role at shop creation, never to check access.
    'default_shop_owner_role' => 'shop-owner',
];
