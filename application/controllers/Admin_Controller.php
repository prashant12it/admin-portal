<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_Controller extends CI_Controller
{
    var $configUpload;

    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization');
        header("Access-Control-Allow-Methods: GET, POST");
        parent::__construct();
        $this->load->model('Common_Model');
        $this->load->library('form_validation');
        $this->load->helper('file');
    }

    public function index()
    {
        $data['success'] = false;
        $data['error'] = false;
        $data['successMsg'] = '';
        $data['errorMsg'] = '';
        $data['szMetaTagTitle'] = "Admin";
        $data['pageName'] = "Admin";
        $data['role'] = 0;
        if ($this->session->userdata('userrole') && $this->session->userdata('userrole') > 0 && $this->session->userdata('userrole') != 1) {
            redirect(__BASE_URL__ . '/page-not-found');
        } elseif ($this->session->userdata('userrole') && $this->session->userdata('userrole') > 0 && $this->session->userdata('userrole') == 1) {
            redirect(__BASE_URL__ . '/dashboard');
        } else {
            $this->load->view('layout/admin-header', $data);
            $this->load->view('admin/login');
            $this->load->view('layout/admin-footer');
        }
    }

    public function valid_password($password = '')
    {
        $password = trim($password);
        $regex_lowercase = '/[a-z]/';
        $regex_uppercase = '/[A-Z]/';
        $regex_number = '/[0-9]/';
        $regex_special = '/[!@#\$%\^\*]/';
        if (empty($password)) {
            $this->form_validation->set_message('valid_password', 'The {field} field is required.');
            return FALSE;
        }
        if (preg_match_all($regex_lowercase, $password) < 1) {
            $this->form_validation->set_message('valid_password', 'The {field} field must have at least one lowercase letter.');
            return FALSE;
        }
        if (preg_match_all($regex_uppercase, $password) < 1) {
            $this->form_validation->set_message('valid_password', 'The {field} field must have at least one uppercase letter.');
            return FALSE;
        }
        if (preg_match_all($regex_number, $password) < 1) {
            $this->form_validation->set_message('valid_password', 'The {field} field must have at least one number.');
            return FALSE;
        }
        if (preg_match_all($regex_special, $password) < 1) {
            $this->form_validation->set_message('valid_password', 'The {field} field must have at least one special character.' . ' ' . htmlentities('!@#$%^*'));
            return FALSE;
        }
        if (strlen($password) < 8) {
            $this->form_validation->set_message('valid_password', 'The {field} field must be at least 8 characters in length.');
            return FALSE;
        }
        if (strlen($password) > 50) {
            $this->form_validation->set_message('valid_password', 'The {field} field cannot exceed 50 characters in length.');
            return FALSE;
        }
        return TRUE;
    }

    function logout()
    {
        if (!$this->session->userdata('user')) {
            redirect(__BASE_URL__);
        } else {
            $this->session->unset_userdata('user');
            $this->session->unset_userdata('userrole');
            $this->session->sess_destroy();
            redirect(__BASE_URL__);
        }
    }

    function dashboard()
    {
        $data['success'] = false;
        $data['successMsg'] = '';
        $data['error'] = false;
        $data['errorMsg'] = '';
        $data['szMetaTagTitle'] = "Admin Dashboard";
        $data['pageName'] = "AdminDashboard";
        $data['role'] = 0;
        if ($this->session->userdata('user') && $this->session->userdata('userrole') == 1) {
            $data['role'] = 1;
            $this->load->view('layout/admin-header', $data);
            $this->load->view('admin/dashboard');
            $this->load->view('layout/admin-footer');
        } else {
            redirect(__BASE_URL__ . '/logout');
        }
    }

    function login()
    {
        $data['success'] = false;
        $data['error'] = false;
        $error = false;
        $data['successMsg'] = '';
        $data['errorMsg'] = '';
        $data['szMetaTagTitle'] = "Admin Login";
        $data['pageName'] = "AdminLogin";
        $data['shop'] = $this->input->post('shop');
        $data['emailid'] = $this->input->post('login');
        $data['password'] = $this->input->post('password');

        $this->form_validation->set_rules('login', 'Email ID', 'trim|required|min_length[7]|max_length[150]|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'callback_valid_password');
        if ($this->form_validation->run() == FALSE) {
            $error = true;
        } else {
            $checkUserExistArr = $this->Common_Model->checkUserExist($data['emailid']);
            if (!empty($checkUserExistArr) && $checkUserExistArr[0]['user_role'] == 1) {
                $storedPassword = $checkUserExistArr[0]['password'];
                if ($this->bcrypt->check_password($data['password'], $storedPassword)) {
                    $this->session->set_userdata('user', $checkUserExistArr[0]['id']);
                    $this->session->set_userdata('userrole', $checkUserExistArr[0]['user_role']);
                    redirect(__BASE_URL__ . '/dashboard');
                } else {
                    $error = true;
                }
            } else {
                $error = true;
            }
        }

        if ($error) {
            $data['errorMsg'] = 'Invalid Login ID or Password.';
            $this->load->view('layout/admin-header', $data);
            $this->load->view('admin/login');
            $this->load->view('layout/admin-footer');
        }
    }

    function jobs()
    {
        $data['szMetaTagTitle'] = "Jobs";
        $data['pageName'] = "Jobs";
        if (!$this->session->userdata('user') && !$this->session->userdata('userrole') && $this->session->userdata('userrole') != 1) {
            redirect(__BASE_URL__ . '/logout');
        } else {
            $data['user'] = $this->session->userdata('user');
            $data['role'] = $this->session->userdata('userrole');
            // Get messages from the session
            if ($this->session->userdata('success_msg')) {
                $data['success_msg'] = $this->session->userdata('success_msg');
                $this->session->unset_userdata('success_msg');
            }
            if ($this->session->userdata('error_msg')) {
                $data['error_msg'] = $this->session->userdata('error_msg');
                $this->session->unset_userdata('error_msg');
            }

            // Get rows
            $data['jobsArr'] = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_JOBS__);

            // Load the list page view
            $this->load->view('layout/admin-header', $data);
            $this->load->view('admin/jobs', $data);
            $this->load->view('layout/admin-footer');
        }
    }

    function companies()
    {
        $data['szMetaTagTitle'] = "Companies";
        $data['pageName'] = "Companies";
        if (!$this->session->userdata('user') && !$this->session->userdata('userrole') && $this->session->userdata('userrole') != 1) {
            redirect(__BASE_URL__ . '/logout');
        } else {
            $data['user'] = $this->session->userdata('user');
            $data['role'] = $this->session->userdata('userrole');
            // Get messages from the session
            if ($this->session->userdata('success_msg')) {
                $data['success_msg'] = $this->session->userdata('success_msg');
                $this->session->unset_userdata('success_msg');
            }
            if ($this->session->userdata('error_msg')) {
                $data['error_msg'] = $this->session->userdata('error_msg');
                $this->session->unset_userdata('error_msg');
            }

            // Get rows
            $data['companyArr'] = $this->Common_Model->getAllCompanies();

            // Load the list page view
            $this->load->view('layout/admin-header', $data);
            $this->load->view('admin/companies', $data);
            $this->load->view('layout/admin-footer');
        }
    }

    function positions()
    {
        $data['szMetaTagTitle'] = "Positions";
        $data['pageName'] = "Positions";
        if (!$this->session->userdata('user') && !$this->session->userdata('userrole') && $this->session->userdata('userrole') != 1) {
            redirect(__BASE_URL__ . '/logout');
        } else {
            $data['user'] = $this->session->userdata('user');
            $data['role'] = $this->session->userdata('userrole');
            // Get messages from the session
            if ($this->session->userdata('success_msg')) {
                $data['success_msg'] = $this->session->userdata('success_msg');
                $this->session->unset_userdata('success_msg');
            }
            if ($this->session->userdata('error_msg')) {
                $data['error_msg'] = $this->session->userdata('error_msg');
                $this->session->unset_userdata('error_msg');
            }

            // Get rows
            $data['positionsArr'] = $this->Common_Model->getAllPositions();

            // Load the list page view
            $this->load->view('layout/admin-header', $data);
            $this->load->view('admin/positions', $data);
            $this->load->view('layout/admin-footer');
        }
    }

    function import_companies()
    {
        if (!$this->session->userdata('user') && !$this->session->userdata('userrole') && $this->session->userdata('userrole') != 1) {
            redirect(__BASE_URL__ . '/logout');
        } else {
            if ($this->input->post('importSubmit')) {
                // Form field validation rules
                $this->form_validation->set_rules('file', 'CSV file', 'callback_file_check');

                // Validate submitted form data
                if ($this->form_validation->run() == true) {
                    $insertCount = $rowCount = $notAddCount = 0;

                    // If file uploaded
                    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                        // Load CSV reader library
                        $target_dir = __FILE_UPLOAD_PATH__;
                        $timeStamp = time();
                        $imageResultArr = array();
                        $target_file = $target_dir . basename($timeStamp . $_FILES["file"]["name"]);
                        $uploadOk = 1;
                        $FileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                        // Allow certain file formats
                        if ($FileType != "csv") {
                            $this->session->set_userdata('error_msg', 'Sorry, only csv files are allowed.');
                            $uploadOk = 0;
                        }
                        // Check if $uploadOk is set to 0 by an error
                        if ($uploadOk == 0) {
                            $this->session->set_userdata('error_msg', 'Sorry, your file was not uploaded.');
                            // if everything is ok, try to upload file
                        } else {
                            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                                $row = 1;
                                if (($handle = fopen($target_file, "r")) !== FALSE) {
                                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                        if (!empty($data)) {

                                            $rowCount++;
                                            $CheckCompanyExist = $this->Common_Model->getAllCompanies(0, $data[0]);
                                            if (empty($CheckCompanyExist) && $this->Common_Model->insertCompany($data[0],(isset($data[1]) && !empty($data[1])?$data[1]:''))) {
                                                $insertCount++;
                                            }
                                            $row++;
                                        }
                                    }
                                    fclose($handle);
                                }

                                $fp = fopen($target_dir . 'file.csv', 'w');

                                fputcsv($fp, $imageResultArr);
                                fclose($fp);
                            } else {
                                $this->session->set_userdata('error_msg', 'Sorry, there was an error uploading your file.');
                            }
                        }
                        // Status message with imported data count
                        $notAddCount = ($rowCount - $insertCount);
                        $successMsg = 'Companies imported successfully. Total Rows (' . $rowCount . ') | Inserted (' . $insertCount . ') | Not Inserted (' . $notAddCount . ')';
                        $this->session->set_userdata('success_msg', $successMsg);
                    } else {
                        $this->session->set_userdata('error_msg', 'Error on file upload, please try again.');
                    }
                    redirect(__BASE_URL__ . '/companies');
                }
            }
        }
    }

    function import_jobs()
    {
        if (!$this->session->userdata('user') && !$this->session->userdata('userrole') && $this->session->userdata('userrole') != 1) {
            redirect(__BASE_URL__ . '/logout');
        } else {
            if ($this->input->post('importSubmit')) {
                // Form field validation rules
                $this->form_validation->set_rules('file', 'CSV file', 'callback_file_check');

                // Validate submitted form data
                if ($this->form_validation->run() == true) {
                    $insertCount = $updateCount = $rowCount = $notAddCount = 0;

                    // If file uploaded
                    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                        // Load CSV reader library
                        $target_dir = __FILE_UPLOAD_PATH__;
                        $timeStamp = time();
                        $imageResultArr = array();
                        $target_file = $target_dir . basename($timeStamp . $_FILES["file"]["name"]);
                        $uploadOk = 1;
                        $FileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                        // Allow certain file formats
                        if ($FileType != "csv") {
                            $this->session->set_userdata('error_msg', 'Sorry, only csv files are allowed.');
                            $uploadOk = 0;
                        }
                        // Check if $uploadOk is set to 0 by an error
                        if ($uploadOk == 0) {
                            $this->session->set_userdata('error_msg', 'Sorry, your file was not uploaded.');
                            // if everything is ok, try to upload file
                        } else {
                            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                                $row = 1;
                                if (($handle = fopen($target_file, "r")) !== FALSE) {
                                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                        if($row>1){
                                            if (!empty($data)) {
                                                $rowCount++;
                                                $postingDate = date('Y-m-d');
                                                $dateArr = explode('/',$data[5]);
                                                if(!empty($dateArr)){
                                                    $postingDate = $dateArr[2].'-'.$dateArr[0].'-'.$dateArr[1];
                                                }
                                                $splitWebURL = explode('://',$data[2]);
                                                $DomainOnly = explode('/',$splitWebURL[1]);
                                                $splitDomain = explode('.',$DomainOnly[0]);
                                                $DomainName = $splitDomain[count($splitDomain)-2].'.'.$splitDomain[count($splitDomain)-1];

                                                $searchArr = array(
                                                    'position' => $data[0],
                                                    'company' => $data[3],
                                                    'location' => $data[4],
                                                    'job_type' => $data[6]
                                                );
                                                $CheckJobExist = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_JOBS__, $searchArr);
                                                if(!empty($CheckJobExist)){
                                                    $dataArr = array(
                                                        'description' => $data[1],
                                                        'website' => $data[2],
                                                        'domain_name' => $DomainName,
                                                        'posting_date' => $postingDate
                                                    );
                                                    if ($this->Common_Model->updateData(__DBC_SCHEMATA_JOBS__, $dataArr, array('id' => $CheckJobExist[0]['id']))) {
                                                        $updateCount++;
                                                    }
                                                }else{
                                                    $jobid = 0;
                                                    $jobsArr = array(
                                                        'position' => $data[0],
                                                        'company' => $data[3],
                                                        'description' => $data[1],
                                                        'website' => $data[2],
                                                        'domain_name' => $DomainName,
                                                        'location' => $data[4],
                                                        'job_type' => $data[6],
                                                        'posting_date' => $postingDate
                                                    );
                                                    $jobid = $this->Common_Model->insertData($jobsArr, __DBC_SCHEMATA_JOBS__);
                                                    if($jobid>0){
                                                        $insertCount++;
                                                    }
                                                }
                                            }
                                        }
                                        $row++;
                                    }
                                    fclose($handle);
                                }

                                $fp = fopen($target_dir . 'file.csv', 'w');

                                fputcsv($fp, $imageResultArr);
                                fclose($fp);
                            } else {
                                $this->session->set_userdata('error_msg', 'Sorry, there was an error uploading your file.');
                            }
                        }
                        // Status message with imported data count
                        $notAddCount = ($rowCount - $insertCount);
                        $successMsg = 'Jobs imported successfully. Total Rows (' . $rowCount . ') | Inserted (' . $insertCount . ') | Not Inserted (' . $notAddCount . ')';
                        $this->session->set_userdata('success_msg', $successMsg);
                    } else {
                        $this->session->set_userdata('error_msg', 'Error on file upload, please try again.');
                    }
                    redirect(__BASE_URL__ . '/jobs');
                }
            }
        }
    }

    function import_positions()
    {
        if (!$this->session->userdata('user') && !$this->session->userdata('userrole') && $this->session->userdata('userrole') != 1) {
            redirect(__BASE_URL__ . '/logout');
        } else {
            if ($this->input->post('importSubmit')) {
                // Form field validation rules
                $this->form_validation->set_rules('file', 'CSV file', 'callback_file_check');

                // Validate submitted form data
                if ($this->form_validation->run() == true) {
                    $insertCount = $rowCount = $notAddCount = 0;

                    // If file uploaded
                    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                        // Load CSV reader library
                        $target_dir = __FILE_UPLOAD_PATH__;
                        $timeStamp = time();
                        $imageResultArr = array();
                        $target_file = $target_dir . basename($timeStamp . $_FILES["file"]["name"]);
                        $uploadOk = 1;
                        $FileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                        // Allow certain file formats
                        if ($FileType != "csv") {
                            $this->session->set_userdata('error_msg', 'Sorry, only csv files are allowed.');
                            $uploadOk = 0;
                        }
                        // Check if $uploadOk is set to 0 by an error
                        if ($uploadOk == 0) {
                            $this->session->set_userdata('error_msg', 'Sorry, your file was not uploaded.');
                            // if everything is ok, try to upload file
                        } else {
                            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                                $row = 1;
                                if (($handle = fopen($target_file, "r")) !== FALSE) {
                                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                        if (!empty($data)) {
                                            $rowCount++;
                                            $CheckPositionExist = $this->Common_Model->getAllPositions(0, $data[0]);
                                            if (empty($CheckPositionExist) && $this->Common_Model->insertPosition($data[0])) {
                                                $insertCount++;
                                            }
                                            $row++;
                                        }
                                    }
                                    fclose($handle);
                                }

                                $fp = fopen($target_dir . 'file.csv', 'w');

                                fputcsv($fp, $imageResultArr);
                                fclose($fp);
                            } else {
                                $this->session->set_userdata('error_msg', 'Sorry, there was an error uploading your file.');
                            }
                        }
                        // Status message with imported data count
                        $notAddCount = ($rowCount - $insertCount);
                        $successMsg = 'Positions imported successfully. Total Rows (' . $rowCount . ') | Inserted (' . $insertCount . ') | Not Inserted (' . $notAddCount . ')';
                        $this->session->set_userdata('success_msg', $successMsg);
                    } else {
                        $this->session->set_userdata('error_msg', 'Error on file upload, please try again.');
                    }
                    redirect(__BASE_URL__ . '/positions');
                }
            }
        }
    }

    public
    function file_check($str)
    {
        $allowed_mime_types = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") {
            $mime = get_mime_by_extension($_FILES['file']['name']);
            $fileAr = explode('.', $_FILES['file']['name']);
            $ext = end($fileAr);
            if (($ext == 'csv') && in_array($mime, $allowed_mime_types)) {
                return true;
            } else {
                $this->form_validation->set_message('file_check', 'Please select only CSV file to upload.');
                return false;
            }
        } else {
            $this->form_validation->set_message('file_check', 'Please select a CSV file to upload.');
            return false;
        }
    }

}