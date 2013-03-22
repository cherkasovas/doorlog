<?php

namespace models;
use core\Db;
use core\Model;

class Departments extends Model {
    public function getAll(){
        $q = "SELECT
              d.id,
              d.name,
              count(personal_id) as total_users,
              t.name as chief_name
            FROM department as d
            LEFT JOIN user as u ON u.department_id = d.id
            LEFT JOIN `tc-db-main`.personal as t ON d.chief_id = t.id
            GROUP BY d.id";
        $params = array();
        $result = $this->fetchAll($q,$params);
        return $result;
    }

    public function getMenuDepartments(){
        $q = "SELECT name, id
              FROM department";
        $params = array();
        $result = $this->fetchAll($q, $params);
        return $result;
    }

    public function getDepById($id){
      $q = "SELECT * FROM department WHERE id = (:id)";
      $params = array();
      $params['id'] = $id; 
      $result = $this->fetchOne($q, $params);
      return $result;
    }

    public function createDep($depName){
      $q = "INSERT INTO department(name) VALUES(:depName)";
      $params = array();
      $params['depName'] = $depName;
      $result = $this->execute($q, $params);

      return $result;
      }

    public function dellDep($id){
      $params = array();
      $params['id'] = $id;
      $q = "DELETE FROM department WHERE id = (:id)";
      $q1 = "UPDATE user SET department_id = '0' WHERE department_id = (:id) ";
      $result = $this->execute($q, $params);
      $result1 = $this->execute($q1, $params);
      return $result;
    }
    
    public function editDep($newname, $id){
      $params = array();
      $params['id'] = $id;
      $params['newname'] = $newname;
      $q = "UPDATE department SET name = (:newname) WHERE id = (:id) ";
      $result = $this->execute($q, $params);
      return $result;
    }

    public function getUsers($depId){
        $depId = (int) $depId;
        $q = "SELECT p.name
            FROM `tc-db-main`.personal as p
            LEFT JOIN `savage-db`.user as u
            ON u.personal_id = p.id
            WHERE u.department_id = " . $depId;
        $params = array();
        $result = $this->fetchAll($q, $params);
        return $result;
    }
}