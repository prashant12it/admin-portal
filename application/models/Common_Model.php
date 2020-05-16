<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: prashantsingh
 * Date: 16/03/20
 * Time: 4:35 PM
 */
class Common_Model extends CI_Model
{

    function __construct()
    {
        // Set table name
    }

    function getAllCompanies($companyID = 0, $companyName = ''){
        if ($companyID > 0) {
            $this->db->where('id', $companyID);
        }
        if (!empty($companyName)) {
            $this->db->where('name', $companyName);
        }
        $query = $this->db->get(__DBC_SCHEMATA_COMPANIES__);
        if ($query->num_rows() > 0) {
            $row = $query->result_array();
            return $row;
        } else {
            return false;
        }
    }

    function getAllPositions($positionID = 0, $positionName = ''){
        if ($positionID > 0) {
            $this->db->where('id', $positionID);
        }
        if (!empty($positionName)) {
            $this->db->where('name', $positionName);
        }
        $query = $this->db->get(__DBC_SCHEMATA_POSITIONS__);
        if ($query->num_rows() > 0) {
            $row = $query->result_array();
            return $row;
        } else {
            return false;
        }
    }

    function insertCompany($company,$logo){
        $insertArr = array(
            'name' => $company,
            'logo' => $logo
        );
        $queryInsert = $this->db->insert(__DBC_SCHEMATA_COMPANIES__, $insertArr);
        if ($queryInsert) {
            return true;
        } else {
            return false;
        }
    }

    function insertData($insertArr,$table){
        $queryInsert = $this->db->insert($table, $insertArr);
        if ($queryInsert) {
            $dataId = $this->db->insert_id();
            return $dataId;
        } else {
            return false;
        }
    }

    function insertPosition($position){
        $insertArr = array(
            'name' => $position
        );
        $queryInsert = $this->db->insert(__DBC_SCHEMATA_POSITIONS__, $insertArr);
        if ($queryInsert) {
            return true;
        } else {
            return false;
        }
    }

    function insertBatchData($tableName,$dataArr){
        $queryInsert = $this->db->insert_batch($tableName, $dataArr);
        if ($queryInsert) {
            return true;
        } else {
            return false;
        }
    }

    function updateData($tableName,$updateArr, $whereArr=array())
    {
        $this->db->where($whereArr);
        $queryUpdate = $this->db->update($tableName, $updateArr);
        if ($queryUpdate) {
            return true;
        } else {
            return false;
        }
    }

    function checkUserExist($emailID)
    {
        $query = $this->db->get_where(__DBC_SCHEMATA_USERS__, array('email' => $emailID));
        if ($query->num_rows() > 0) {
            $row = $query->result_array();
            return $row;
        } else {
            return false;
        }
    }

    function getUserData($userID = 0, $role = 0){
        if ($userID > 0) {
            $this->db->where('id', $userID);
        }
        if ($role > 0) {
            $this->db->where('user_role', $role);
        }
        $query = $this->db->get(__DBC_SCHEMATA_USERS__);
        if ($query->num_rows() > 0) {
            $row = $query->result_array();
            return $row;
        } else {
            return false;
        }
    }


    function getCompleteData($tableName, $filterArr = array()){
        if (!empty($filterArr)) {
            $this->db->where($filterArr);
        }
        $query = $this->db->get($tableName);
        if ($query->num_rows() > 0) {
            $row = $query->result_array();
            return $row;
        } else {
            return false;
        }
    }

    function deleteRecord($tableName,$filterArr)
    {
        $query = $this->db->where($filterArr)->delete($tableName);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

}