<?php
/**
 * @author adrenaline
 */
namespace controllers;

use core\Controller;
use models\Roles as Ro;
use core\FlashMessages;

class Roles extends Controller{

    function indexAction(){
        $obj = new Ro();
        $roles = $obj->getAll();
        $this->render("Roles/index.tpl" , array('roles' => $roles) );
    }

    function addAction(){
        $obj =  new Ro();
        if(isset($_POST['roleName']) && $_POST['roleName']){
           $roleName = $_POST['roleName'];
           $obj->addRole($roleName);
           FlashMessages::addMessage("Роль успешно добавлена.", "info");
        }

        $this->render("Roles/add.tpl", array('value'=>0));
    }

    function editAction(){
        $obj = new Ro();

        $rolePermissions = array();
        $allPermissions = array();

        if(isset($_GET['id']) && $_GET['id']){
            $roleId = $_GET['id'];
            $rolePermissions = $obj->getRolePermissions($roleId);
        }

        foreach ($rolePermissions as $result){
            $rolePermissions= $result;
        }

        $allPermissions = $obj->getAllPermissions();

        $sortedPermissions = array();
        foreach($allPermissions as $row){
            $sortedPermissions[$row['id']]['group_name'] = $row['group_name'];
            $sortedPermissions[$row['id']]['permissions'][$row['perm_id']]= $row['perm_name'];
           

        }

        $this->render("Roles/edit.tpl", array('rolePermissions'=>$rolePermissions,
                                              'allPermissions'=>$sortedPermissions));
    }

    
}

?>
