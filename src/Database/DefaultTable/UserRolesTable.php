<?php

namespace MiniCore\Database\DefaultTable;

use MiniCore\Database\Table;
use MiniCore\Database\Action\DataAction;

/**
 * Class UserRolesTable
 *
 * This class manages the `user_roles` table, providing methods to assign, remove, and check user roles.
 * It enables role-based access control (RBAC) by linking users to specific roles.
 *
 * Table structure:
 * - `id`: Unique identifier for each user-role entry (auto-increment).
 * - `userId`: The ID of the user associated with the role.
 * - `roleId`: The ID of the role assigned to the user.
 *
 * @example
 * $userRolesTable = new UserRolesTable();
 *
 * // Assign a role to a user
 * $userRolesTable->addRoleToUser(1, 2);
 *
 * // Check if a user has a role
 * $hasRole = $userRolesTable->hasRole(1, 2);
 *
 * // Remove a role from a user
 * $userRolesTable->removeRoleFromUser(1, 2);
 */
class UserRolesTable extends Table
{
    /**
     * UserRolesTable constructor.
     *
     * Initializes the `user_roles` table with its structure and columns.
     */
    public function __construct()
    {
        parent::__construct(
            'user_roles',
            [
                'id'     => 'INT AUTO_INCREMENT PRIMARY KEY', // Unique ID
                'userId' => 'INT NOT NULL',                  // User ID
                'roleId' => 'INT NOT NULL',                  // Role ID
            ]
        );
    }

    /**
     * Get all roles assigned to a user.
     *
     * @param int $userId The ID of the user.
     * @return array List of role IDs assigned to the user.
     *
     * @example
     * $roles = $userRolesTable->getRolesByUserId(1);
     */
    public function getRolesByUserId(int $userId): array
    {
        $dataAction = new DataAction();
        $dataAction->addColumn('roleId');
        $dataAction->addProperty('WHERE', 'userId = :userId', ['userId' => $userId]);
        return $this->actions['select']->execute($dataAction);
    }

    /**
     * Get all users who have a specific role.
     *
     * @param int $roleId The ID of the role.
     * @return array List of user IDs with the specified role.
     *
     * @example
     * $users = $userRolesTable->getUsersByRoleId(2);
     */
    public function getUsersByRoleId(int $roleId): array
    {
        $dataAction = new DataAction();
        $dataAction->addColumn('userId');
        $dataAction->addProperty('WHERE', 'roleId = :roleId', ['roleId' => $roleId]);
        return $this->actions['select']->execute($dataAction);
    }

    /**
     * Assign a role to a user.
     *
     * @param int $userId The ID of the user.
     * @param int $roleId The ID of the role.
     * @return bool True if the role was successfully assigned, false otherwise.
     *
     * @example
     * $userRolesTable->addRoleToUser(1, 2);
     */
    public function addRoleToUser(int $userId, int $roleId): bool
    {
        $dataAction = new DataAction();
        $dataAction->addColumn('userId');
        $dataAction->addColumn('roleId');

        $dataAction->addParameters([
            'userId' => $userId,
            'roleId' => $roleId,
        ]);

        return $this->actions['insert']->execute($dataAction);
    }

    /**
     * Remove a role from a user.
     *
     * @param int $userId The ID of the user.
     * @param int $roleId The ID of the role.
     * @return bool True if the role was successfully removed, false otherwise.
     *
     * @example
     * $userRolesTable->removeRoleFromUser(1, 2);
     */
    public function removeRoleFromUser(int $userId, int $roleId): bool
    {
        $dataAction = new DataAction();

        $dataAction->addProperty('WHERE', 'userId = :userId AND roleId = :roleId', [
            'userId' => $userId,
            'roleId' => $roleId,
        ]);

        return $this->actions['delete']->execute($dataAction);
    }

    /**
     * Check if a user has a specific role.
     *
     * @param int $userId The ID of the user.
     * @param int $roleId The ID of the role.
     * @return bool True if the user has the role, false otherwise.
     *
     * @example
     * if ($userRolesTable->hasRole(1, 2)) {
     *     echo "User has the role.";
     * }
     */
    public function hasRole(int $userId, int $roleId): bool
    {
        $dataAction = new DataAction();
        $dataAction->addColumn('id');

        $dataAction->addProperty('WHERE', 'userId = :userId AND roleId = :roleId', [
            'userId' => $userId,
            'roleId' => $roleId,
        ]);

        $dataAction->addProperty('LIMIT', '1');
        $result = $this->actions['select']->execute($dataAction);
        return !empty($result);
    }
}
