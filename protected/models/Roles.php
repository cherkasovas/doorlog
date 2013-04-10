<?php

/**
 * @author adrenaline
 */
namespace models;
use core\Db;
use core\Model;

class Roles extends Model {

    public function getAll(){
        $q = "SELECT name, id, count(user_id) as users_count
               FROM role
               LEFT JOIN users_roles as ur
                      ON role.id = ur.role_id
               GROUP BY name";
        $result = $this->fetchAll($q);
        return $result;
    }

    public function addRole($name){
        $params = array();
        $params['name'] = $name;
        $q = "INSERT INTO role(name)
                   VALUES(:name)";
        $result = $this->execute($q, $params);
        return $result;
    }

    public function getRolePermissions($roleId){
        $params = array();
        $params['roleId'] = $roleId;
        $q = "SELECT rp.permission_id, p.key
              FROM roles_permissions rp
              JOIN permission p
              ON p.id = rp.permission_id
              WHERE role_id = :roleId";
        $result = $this->fetchAll($q, $params);
        return $result;
    }

    public function getAllPermissions(){

        $q = "SELECT pg.id, pg.name as group_name, p.name as perm_name, p.id as perm_id
              FROM permission_group pg
              JOIN permission p
                ON p.permission_group_id = pg.id
              ORDER BY id ASC";
        $result = $this->fetchAll($q);
        return $result;
    }

    public function deleteRolePermissions($roleId){
        $q = "DELETE FROM roles_permissions
              WHERE role_id = (:roleId)";
        $params = array();
        $params['roleId'] = $roleId;
        $result = $this->execute($q, $params);
        return $result;
    }

    public function addRolePermissions($roleId, $permission){
        $q = "INSERT INTO roles_permissions(role_id, permission_id)
              VALUES(:roleId, :permission)";
        $params = array();
        $params['roleId'] = $roleId;
        $params['permission'] = $permission;
        $result = $this->execute($q, $params);
        return $result;
    }

    public function deleteRoleWithPermissions($roleId){
        $this->deleteRolePermissions($roleId);
        $q = "DELETE FROM role
              WHERE id = (:roleId)";
        $q1 = "DELETE FROM users_roles
               WHERE role_id = (:roleId)";
        $params = array();
        $params['roleId'] = $roleId;
        $result = $this->execute($q, $params);
        $result1 = $this->execute($q1, $params);
        return $result;
    }

    public function insertUserRole($user_id, $role_id){
        $q="INSERT INTO users_roles(user_id, role_id)
            VALUES (:user_id, :role_id)";
        $params = array();
        $params['user_id'] = $user_id;
        $params['role_id'] = $role_id;
        $result = $this->execute($q, $params);
        return $result;
    }

    public function editUserRole($user_id, $role_id){
        $q = "UPDATE users_roles SET role_id = (:role_id) WHERE user_id = (:user_id)";
        $params = array();
        $params['user_id'] = $user_id;
        $params['role_id'] = $role_id;
        $result = $this->execute($q, $params);
        return $result;
    }

    public function getUserRole($user_id){
        $q = "SELECT r.id, r.name
            FROM role as r
            JOIN users_roles as ur
              ON ur.role_id = r.id
            WHERE ur.user_id = :user_id";
        $params = array();
        $params['user_id'] = $user_id;
        $result = $this->fetchAll($q, $params);
        return $result;
    }
}
