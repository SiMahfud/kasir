<?php

if (!function_exists('isLoggedIn')) {
    /**
     * Checks if a user is currently logged in.
     *
     * @return bool
     */
    function isLoggedIn(): bool
    {
        return session()->get('isLoggedIn') ?? false;
    }
}

if (!function_exists('hasPermission')) {
    /**
     * Checks if the logged-in user has a specific permission.
     * Also implicitly checks if the user is logged in.
     *
     * @param string $permissionKey The permission key to check (e.g., 'users_create').
     * @return bool True if the user has the permission, false otherwise.
     */
    function hasPermission(string $permissionKey): bool
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return false;
        }

        $userPermissions = $session->get('user_permissions');
        if (!is_array($userPermissions)) {
            return false; // Or log this as an anomaly
        }

        return in_array($permissionKey, $userPermissions);
    }
}

if (!function_exists('isAdmin')) {
    /**
     * Checks if the logged-in user has the 'admin' role.
     *
     * @return bool
     */
    function isAdmin(): bool
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return false;
        }
        return $session->get('user_role') === 'admin';
    }
}

if (!function_exists('isStaff')) {
    /**
     * Checks if the logged-in user has the 'staff' role.
     *
     * @return bool
     */
    function isStaff(): bool
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return false;
        }
        return $session->get('user_role') === 'staff';
    }
}

if (!function_exists('currentUserRole')) {
    /**
     * Gets the role of the currently logged-in user.
     *
     * @return string|null The user's role name or null if not logged in.
     */
    function currentUserRole(): ?string
    {
        return session()->get('user_role');
    }
}

if (!function_exists('currentUserId')) {
    /**
     * Gets the ID of the currently logged-in user.
     *
     * @return int|null The user's ID or null if not logged in.
     */
    function currentUserId(): ?int
    {
        $userId = session()->get('user_id');
        return $userId ? (int)$userId : null;
    }
}

if (!function_exists('currentUserName')) {
    /**
     * Gets the name of the currently logged-in user.
     *
     * @return string|null The user's name or null if not logged in.
     */
    function currentUserName(): ?string
    {
        return session()->get('user_name');
    }
}
