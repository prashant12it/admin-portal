<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include __APP_PATH__ . '/vendor/autoload.php';

class Api_Controller extends CI_Controller
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
        $this->load->library('email');
        ini_set('max_input_time', 900);
        ini_set('max_execution_time', 900);
        $this->configUpload['upload_path'] = __FILE_UPLOAD_PATH__;
        $this->configUpload['allowed_types'] = 'pdf';
        $this->configUpload['max_size'] = '10000000';
        $this->configUpload['encrypt_name'] = TRUE;
    }

    public function index()
    {
        $responsedata = array("code" => 200, "response" => 'Welcome to Job Portal.');
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    public function getCompanies(){
        $companyArr = $this->Common_Model->getAllCompanies();
        if (!empty($companyArr)) {
            $responsedata = array("code" => 200, "dataArr" => $companyArr);
        } else {
            $responsedata = array("code" => 201, "response" => 'No companies found.');
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    public function getPositions(){
        $positionsArr = $this->Common_Model->getAllPositions();
        if (!empty($positionsArr)) {
            $responsedata = array("code" => 200, "dataArr" => $positionsArr);
        } else {
            $responsedata = array("code" => 201, "response" => 'No positions found.');
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    public function saveUser()
    {
        $data['name'] = $this->input->post('name');
        $data['email'] = $this->input->post('email');

        $this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[2]|max_length[150]');
        $this->form_validation->set_rules('email', 'Email ID', 'trim|required|min_length[7]|max_length[150]|valid_email');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $checkUserExistArr = $this->Common_Model->checkUserExist($data['email']);
            if (!empty($checkUserExistArr)) {
                $responsedata = array("code" => 200, "response" => 'User already exist.', 'userID' => $checkUserExistArr[0]['id']);
            } else {
                $userID = $this->Common_Model->insertData($data, __DBC_SCHEMATA_USERS__);
                if ($userID > 0) {
                    $responsedata = array("code" => 200, "response" => 'User saved successfully.', 'userID' => $userID);
                } else {
                    $responsedata = array("code" => 201, "response" => 'Something goes wrong while saving user data.');
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    public function save_listing()
    {
        $data['company_id'] = $this->input->post('company_id');
        $data['position_id'] = $this->input->post('position_id');
        $data['listing_type'] = $this->input->post('listing_type');
        $data['user_id'] = $this->input->post('user_id');

        $this->form_validation->set_rules('company_id', 'Company', 'trim|required');
        $this->form_validation->set_rules('position_id', 'Position', 'trim|required');
        $this->form_validation->set_rules('listing_type', 'Listing', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $companyData = $this->Common_Model->getAllCompanies($data['company_id']);
            if (!empty($companyData)) {
                $data['company_email'] = ($data['listing_type'] != 4 ? 'noreply@' : 'hradmin@') . str_replace(' ', '', $companyData[0]['name']) . '.com';
                $data['added_on'] = date('Y-m-d H:i:s');
                $jobID = $this->Common_Model->insertData($data, __DBC_SCHEMATA_JOB_LISTING__);
                if ($jobID > 0) {
                    $responsedata = array("code" => 200, "response" => 'Manual job added successfully.', 'jobID' => $jobID);
                } else {
                    $responsedata = array("code" => 201, "response" => 'Something goes wrong while adding manual job.');
                }
            } else {
                $responsedata = array("code" => 201, "response" => 'Invalid company.');
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    public function reply_email()
    {
        $data['reply_subject'] = $this->input->post('reply_subject');
        $data['mail_body'] = $this->input->post('mail_body');
        $data['reply_body'] = $this->input->post('reply_body');
        $data['receiver_emailid'] = $this->input->post('receiver_emailid');
        $data['sender_email'] = $this->input->post('sender_email');
        $data['sender_name'] = $this->input->post('sender_name');

        $this->form_validation->set_rules('reply_subject', 'Subject', 'trim|required');
        $this->form_validation->set_rules('mail_body', 'Received Email', 'trim|required');
        $this->form_validation->set_rules('reply_body', 'Reply Mail Content', 'trim|required');
        $this->form_validation->set_rules('receiver_emailid', 'Receiver Email Address', 'trim|required');
        $this->form_validation->set_rules('sender_email', 'Sender Email Address', 'trim|required');
        $this->form_validation->set_rules('sender_name', 'Sender Name', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $this->email->from($data['sender_email'], $data['sender_name']);
            $this->email->to($data['receiver_emailid']);
//            $this->email->to('prashant@flyingcheckout.com');

            $this->email->subject($data['reply_subject']);
            $Htmlmessage = '<!DOCTYPE html>
                                    <html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
                                    <head>
                                        <meta charset="utf-8"> 
                                        <meta name="viewport" content="width=device-width">
                                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                                        <meta name="x-apple-disable-message-reformatting">
                                        <title>Air Suggest | A place to upsell outside products</title>
                                    </head><body>
                                    <div style="margin: 20px 0px 0px 0px">' . $data['reply_body'] . '</div>
                                    <hr style="margin: 20px 0px" />
                                    <div>' . $data['mail_body'] . '</div>
                                    </body></html>';
            $this->email->message($Htmlmessage);
            $this->email->set_mailtype('html');
            if ($this->email->send()) {
                $responsedata = array("code" => 200, "response" => 'Your email sent successfully.');
            } else {
                $responsedata = array("code" => 201, "response" => 'Something goes wrong while sending your email. Please try later.');
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    function saveUploadedResume()
    {
        $data['user_id'] = $this->input->post('user_id');
        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $this->load->library('upload', $this->configUpload);
            $actualName = $_FILES['datafile']['name'];
            $valid_formats = explode('|', $this->configUpload['allowed_types']);
            $fileParts = pathinfo($actualName);
            if ($_FILES["datafile"]["size"] > $this->configUpload['max_size']) {
                $responsedata = array("code" => 202, "response" => 'ERROR', 'message' => 'File size can not be greater than 50MB.');
            } elseif ($_FILES["datafile"]["size"] == 0) {
                $responsedata = array("code" => 202, "response" => 'ERROR', 'message' => 'Invalid file size. File size must be greater than 0byte');
            } else {
                if (in_array(strtolower($fileParts['extension']), $valid_formats)) {
                    $this->upload->do_upload('datafile');
                    $upload_data = $this->upload->data();
                    $dataArr['file_name'] = $upload_data['file_name'];
                    if (CheckFileUploaded($dataArr['file_name'])) {
                        $resumeData = $this->parseMyResume(__FILE_UPLOAD_PATH__ . $dataArr['file_name']);
                        $insertedSkills = true;
                        $insertedExp = true;
                        $insertedEdu = true;
                        if (!empty($resumeData)) {
                            $skillsArr = $resumeData['skills'];
                            $experienceArr = $resumeData['experience'];
                            $educationArr = $resumeData['education'];
                            if (!empty($skillsArr)) {
                                $inserSkillsDataArr = array();
                                foreach ($skillsArr as $skill) {
                                    $userSkill = array(
                                        'user_id' => $data['user_id'],
                                        'skill' => $skill
                                    );
                                    array_push($inserSkillsDataArr, $userSkill);
                                }
                                $insertedSkills = $this->Common_Model->insertBatchData(__DBC_SCHEMATA_SKILLS__, $inserSkillsDataArr);
                            }
                            if (!empty($experienceArr)) {
                                foreach ($experienceArr as $experience) {
                                    $expdata = array();
                                    $expdata['user_id'] = $data['user_id'];
                                    $expdata['position'] = $experience['position'];
                                    $expdata['company'] = $experience['company'];
                                    $expdata['location'] = $experience['location'];
                                    $expdata['from_month'] = $experience['from_month'];
                                    $expdata['from_year'] = $experience['from_year'];
                                    $expdata['to_month'] = $experience['to_month'];
                                    $expdata['to_year'] = $experience['to_year'];
                                    $expdata['present_working'] = $experience['present_working'];
                                    $ExperienceID = $this->Common_Model->insertData($expdata, __DBC_SCHEMATA_EXPERIENCE__);
                                    if ($ExperienceID > 0) {
                                        $insertedExp = true;
                                    } else {
                                        $insertedExp = false;
                                        break;
                                    }
                                }
                            }
                            if (!empty($educationArr)) {
                                foreach ($educationArr as $education) {
                                    $edudata = array();
                                    $edudata['user_id'] = $data['user_id'];
                                    $edudata['school'] = $education['school'];
                                    $edudata['degree'] = $education['degree'];
                                    $edudata['study_field'] = $education['study_field'];
                                    $edudata['from_year'] = $education['from_year'];
                                    $edudata['to_year'] = (int)trim($education['to_year']);
                                    $edudata['grade'] = '--';
                                    $EducationID = $this->Common_Model->insertData($edudata, __DBC_SCHEMATA_EDUCATION__);
                                    if ($EducationID > 0) {
                                        $insertedEdu = true;
                                    } else {
                                        $insertedEdu = false;
                                        break;
                                    }
                                }
                            }
                            if ($insertedSkills && $insertedExp && $insertedEdu) {
                                $responsedata = array("code" => 200, "response" => 'You profile created successfully from your resume.');
                            } else {
                                $responsedata = array("code" => 201, "response" => 'Something goes wrong while creating your profile from your resume.');
                            }
                        } else {
                            $responsedata = array("code" => 201, "response" => 'No valid data found in your resume.');
                        }
                    } else {
                        $responsedata = array("code" => 201, "response" => 'Something goes wrong while saving your resume.');
                    }
                } else {
                    $responsedata = array("code" => 202, "response" => 'ERROR', 'message' => 'Invalid File Format. Allowed file formats are pdf.');
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    function save_resume()
    {
        $data['user_id'] = $this->input->post('user_id');
        $data['site_url'] = $this->input->post('site_url');
        $data['company_name'] = $this->input->post('company_name');
        $data['company_domain'] = $this->input->post('company_domain');
        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        $this->form_validation->set_rules('site_url', 'Site URL', 'trim|required');
        $this->form_validation->set_rules('company_name', 'Company Name', 'trim|required');
        $this->form_validation->set_rules('company_domain', 'Company Domain', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $this->configUpload['allowed_types'] = 'doc|docx|pdf';
            $this->load->library('upload', $this->configUpload);
            $actualName = $_FILES['resume_file']['name'];
            $valid_formats = explode('|', $this->configUpload['allowed_types']);
            $fileParts = pathinfo($actualName);
            if ($_FILES["resume_file"]["size"] > $this->configUpload['max_size']) {
                $responsedata = array("code" => 202, "response" => 'ERROR', 'message' => 'File size can not be greater than 50MB.');
            } elseif ($_FILES["resume_file"]["size"] == 0) {
                $responsedata = array("code" => 202, "response" => 'ERROR', 'message' => 'Invalid file size. File size must be greater than 0byte');
            } else {
                if (in_array(strtolower($fileParts['extension']), $valid_formats)) {
                    $this->upload->do_upload('resume_file');
                    $upload_data = $this->upload->data();
                    $dataArr['file_name'] = $upload_data['file_name'];
                    if (CheckFileUploaded($dataArr['file_name'])) {
                        $inserDataArr = array(
                            'user_id' => $data['user_id'],
                            'site_url' => $data['site_url'],
                            'resume_file_name' => $dataArr['file_name'],
                            'company_name' => $data['company_name'],
                            'company_domain' => $data['company_domain'],
                            'original_file_name' => $actualName,
                            'uploaded_on' => date('Y-m-d H:i:s')
                        );
                        if ($this->Common_Model->insertData($inserDataArr, __DBC_SCHEMATA_RESUME__)) {
                            $responsedata = array("code" => 200, "response" => 'SUCCESS', 'message' => 'Your resume uploaded successfully.', 'fileurl' => __BASE_URL__ . '/uploads/' . $dataArr['file_name']);
                        } else {
                            $responsedata = array("code" => 201, "response" => 'ERROR', 'message' => 'Something goes wrong while saving your resume.');
                        }
                    } else {
                        $responsedata = array("code" => 201, "response" => 'ERROR', 'message' => 'Something goes wrong while saving your resume.');
                    }
                } else {
                    $responsedata = array("code" => 202, "response" => 'ERROR', 'message' => 'Invalid File Format. Allowed file formats are pdf.');
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    function save_skills()
    {
        $data['skillset'] = $this->input->post('skillset');
        $data['user_id'] = $this->input->post('user_id');

        $this->form_validation->set_rules('skillset', 'Skills', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            if (!empty($data['skillset'])) {
                $skillsArr = explode(',', $data['skillset']);
                $inserDataArr = array();
                if (!empty($skillsArr)) {
                    foreach ($skillsArr as $skill) {
                        $userSkill = array(
                            'user_id' => $data['user_id'],
                            'skill' => $skill
                        );
                        array_push($inserDataArr, $userSkill);
                    }
                }
                if (!empty($inserDataArr)) {
                    if ($this->Common_Model->insertBatchData(__DBC_SCHEMATA_SKILLS__, $inserDataArr)) {
                        $skillSet = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_SKILLS__, array('user_id' => $data['user_id']));
                        if (!empty($skillSet)) {
                            $responsedata = array("code" => 200, "message" => 'Skills Saved Successfully', 'dataArr' => $skillSet);
                        } else {
                            $responsedata = array("code" => 201, "message" => 'Something goes wrong while saving your skills. Please try later.');
                        }
                    } else {
                        $responsedata = array("code" => 201, "message" => 'Something goes wrong while saving your skills. Please try later.');
                    }
                } else {
                    $responsedata = array("code" => 201, "message" => 'Something goes wrong while saving your skills. Please try later.');
                }
            } else {
                $responsedata = array("code" => 201, "message" => 'Something goes wrong while saving your skills. Please try later.');
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    function get_my_skills()
    {
        $data['user_id'] = $this->input->post('user_id');

        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $skillSet = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_SKILLS__, array('user_id' => $data['user_id']));
            if (!empty($skillSet)) {
                $responsedata = array("code" => 200, 'dataArr' => $skillSet);
            } else {
                $responsedata = array("code" => 201, "message" => 'No skills found.');
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    function remove_my_skills()
    {
        $data['user_id'] = $this->input->post('user_id');
        $data['skill_id'] = $this->input->post('skill_id');

        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        $this->form_validation->set_rules('skill_id', 'Skill', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            if ($this->Common_Model->deleteRecord(__DBC_SCHEMATA_SKILLS__, array('id' => $data['skill_id']))) {
                $skillSet = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_SKILLS__, array('user_id' => $data['user_id']));
                if (!empty($skillSet)) {
                    $responsedata = array("code" => 200, 'dataArr' => $skillSet);
                } else {
                    $responsedata = array("code" => 201, "message" => 'No skills found.');
                }
            } else {
                $responsedata = array("code" => 201, "message" => 'No skills found.');
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    function save_experience(){
        $data['user_id'] = $this->input->post('user_id');
        $data['position'] = $this->input->post('position');
        $data['company'] = $this->input->post('company');
        $data['location'] = $this->input->post('location');
        $data['from_month'] = $this->input->post('from_month');
        $data['from_year'] = $this->input->post('from_year');
        $data['to_month'] = $this->input->post('to_month');
        $data['to_year'] = $this->input->post('to_year');
        $data['present_working'] = $this->input->post('present_working');

        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        $this->form_validation->set_rules('position', 'Position', 'trim|required');
        $this->form_validation->set_rules('company', 'Company', 'trim|required');
        $this->form_validation->set_rules('location', 'Location', 'trim|required');
        $this->form_validation->set_rules('from_month', 'Start Date', 'trim|required');
        $this->form_validation->set_rules('from_year', 'Start Date', 'trim|required');
        $this->form_validation->set_rules('to_month', 'End Date', 'trim|required');
        $this->form_validation->set_rules('to_year', 'End Date', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $experienceID = $this->Common_Model->insertData($data, __DBC_SCHEMATA_EXPERIENCE__);
            if ($experienceID > 0) {
                $experienceArr = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_EXPERIENCE__, array('user_id' => $data['user_id']));
                if (!empty($experienceArr)) {
                    $responsedata = array("code" => 200, "response" => 'Experience added successfully.', 'dataArr' => $experienceArr);
                } else {
                    $responsedata = array("code" => 201, "response" => 'Something goes wrong while adding Experience.');
                }
            } else {
                $responsedata = array("code" => 201, "response" => 'Something goes wrong while adding Experience.');
            }

        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    function get_my_experience(){
        $data['user_id'] = $this->input->post('user_id');

        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $experienceArr = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_EXPERIENCE__, array('user_id' => $data['user_id']));
            if (!empty($experienceArr)) {
                $responsedata = array("code" => 200, 'dataArr' => $experienceArr);
            } else {
                $responsedata = array("code" => 201, "message" => 'No experience found.');
            }

        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    function save_education()
    {
        $data['user_id'] = $this->input->post('user_id');
        $data['school'] = $this->input->post('school');
        $data['degree'] = $this->input->post('degree');
        $data['study_field'] = $this->input->post('study_field');
        $data['from_year'] = $this->input->post('from_year');
        $data['to_year'] = $this->input->post('to_year');
        $data['grade'] = $this->input->post('grade');

        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        $this->form_validation->set_rules('school', 'School', 'trim|required');
        $this->form_validation->set_rules('degree', 'Degree', 'trim|required');
        $this->form_validation->set_rules('study_field', 'Field of study', 'trim|required');
        $this->form_validation->set_rules('from_year', 'Start Year', 'trim|required');
        $this->form_validation->set_rules('to_year', 'End Year', 'trim|required');
        $this->form_validation->set_rules('grade', 'Grade', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $educationID = $this->Common_Model->insertData($data, __DBC_SCHEMATA_EDUCATION__);
            if ($educationID > 0) {
                $educationArr = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_EDUCATION__, array('user_id' => $data['user_id']));
                if (!empty($educationArr)) {
                    $responsedata = array("code" => 200, "response" => 'Education added successfully.', 'dataArr' => $educationArr);
                } else {
                    $responsedata = array("code" => 201, "response" => 'Something goes wrong while adding Education.');
                }
            } else {
                $responsedata = array("code" => 201, "response" => 'Something goes wrong while adding Education.');
            }

        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    function get_my_education()
    {
        $data['user_id'] = $this->input->post('user_id');

        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $educationArr = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_EDUCATION__, array('user_id' => $data['user_id']));
            if (!empty($educationArr)) {
                $responsedata = array("code" => 200, 'dataArr' => $educationArr);
            } else {
                $responsedata = array("code" => 201, "message" => 'No experience found.');
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    function check_user_password_set()
    {
        $data['user_email'] = $this->input->post('user_email');
        $this->form_validation->set_rules('user_email', 'User', 'trim|required|min_length[7]|max_length[150]|valid_email');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $userData = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_USERS__, array('email' => $data['user_email']));
            if (!empty($userData) && !empty($userData[0]['password'])) {
                $responsedata = array("code" => 200, 'emptyPassword' => '0');
            } elseif (!empty($userData) && empty($userData[0]['password'])) {
                $responsedata = array("code" => 200, "emptyPassword" => '1');
            } else {
                $responsedata = array("code" => 201, "message" => 'User not found.');
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    function save_password()
    {
        $data['password'] = $this->input->post('password');
        $data['user_id'] = $this->input->post('user_id');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responsedata = array("code" => 400, "response" => 'ERROR', 'message' => $this->form_validation->error_array());
        } else {
            $encryptedPassword = $this->bcrypt->hash_password($data['password']);
            $dataArr = array('password' => $encryptedPassword);
            if ($this->Common_Model->updateData(__DBC_SCHEMATA_USERS__, $dataArr, array('id' => $data['user_id']))) {
                $responsedata = array("code" => 200, 'message' => 'Password saved successfully.');
            } else {
                $responsedata = array("code" => 201, "message" => 'Something goes wrong. Please try later.');
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responsedata);
    }

    public function parseMyResume($uploadfile)
    {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($uploadfile);
//        $pdf    = $parser->parseFile(__FILE_UPLOAD_PATH__.'Profile2.pdf');

        $text = $pdf->getText();
        $textAr = explode("\n", $text);
        $textAr = array_filter($textAr, 'trim');
        $SkillsKey = array_search('Top Skills', $textAr);
        $SummaryKey = array_search('Summary', $textAr);
        $ExperienceKey = array_search('Experience', $textAr);
        $EducationKey = array_search('Education', $textAr);
        $dataArr['skills'] = [];
        $dataArr['experience'] = [];
        $dataArr['education'] = [];
        for ($i = ($SkillsKey + 1); $i < ($SummaryKey - 3); $i++) {
            array_push($dataArr['skills'], $textAr[$i]);
        }
        $j = 0;
        for ($i = ($ExperienceKey + 1); $i < $EducationKey; $i = $i + 4) {
            $dataArr['experience'][$j]['company'] = $textAr[$i];
            $dataArr['experience'][$j]['position'] = $textAr[$i + 1];
            $dataArr['experience'][$j]['location'] = $textAr[$i + 3];
            $periodRange = explode(' ', $textAr[$i + 2]);
            $periodRange1 = array_map('trim', explode($periodRange[1][6], $textAr[$i + 2]));
            if (!empty($periodRange1)) {
                $startDateArr = explode(' ', $periodRange1[0]);
                $dataArr['experience'][$j]['from_month'] = date('m', strtotime($startDateArr[0]));
                $dataArr['experience'][$j]['from_year'] = $startDateArr[1];
                $pos = strpos($periodRange1[1], 'Present');
                if ($pos) {
                    $dataArr['experience'][$j]['to_month'] = date('m');
                    $dataArr['experience'][$j]['to_year'] = date('Y');
                    $dataArr['experience'][$j]['present_working'] = 1;
                } else {
                    $endDateArr = explode(' ', $periodRange1[1]);
                    $dataArr['experience'][$j]['to_month'] = date('m', strtotime($endDateArr[0]));
                    $dataArr['experience'][$j]['to_year'] = $endDateArr[1];
                    $dataArr['experience'][$j]['present_working'] = 0;
                }

            }
            $j++;
        }
        $k = 0;

        for ($i = ($EducationKey + 1); $i < count($textAr) - 1; $i = $i + 2) {
            if (!empty($textAr[$i])) {
                $dataArr['education'][$k]['school'] = $textAr[$i];
                $eduSecArr = explode('(', $textAr[$i + 1]);
                $eduSecArr1 = explode(',', $eduSecArr[0]);
                $eduSecArr2 = explode('-', $eduSecArr[1]);

                $dataArr['education'][$k]['degree'] = $eduSecArr1[0];
                $dataArr['education'][$k]['study_field'] = substr($eduSecArr1[1], 0, -7);
                $dataArr['education'][$k]['from_year'] = $eduSecArr2[0];
                $dataArr['education'][$k]['to_year'] = substr($eduSecArr2[1], 2, -1);
                $k++;
            }
        }
//        print_r($dataArr);
        return $dataArr;
    }

    function linkedinCallBack()
    {
        $AuthCode = $_GET['code'];
        $state = $_GET['state'];
        $responseData = array("code" => 200, "response" => 'SUCCESS', 'AuthCode' => $AuthCode, 'state' => $state);
        header('Content-Type: application/json');
        echo json_encode($responseData);
    }

    function login()
    {
        $data['emailid'] = $this->input->post('emailid');
        $data['password'] = $this->input->post('password');

        $this->form_validation->set_rules('emailid', 'Email ID', 'trim|required|min_length[7]|max_length[150]|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responseData = array("code" => 201, "response" => 'SUCCESS', 'message' => 'Invalid email id or password.');
        } else {
            $checkUserExistArr = $this->Common_Model->checkUserExist($data['emailid']);
            if (!empty($checkUserExistArr)) {
                $storedPassword = $checkUserExistArr[0]['password'];
                if ($this->bcrypt->check_password($data['password'], $storedPassword)) {
                    $responseData = array("code" => 200, "response" => 'SUCCESS', 'userid' => $checkUserExistArr[0]['id']);
                } else {
                    $responseData = array("code" => 201, "response" => 'SUCCESS', 'message' => 'Invalid email id or password.');
                }
            } else {
                $responseData = array("code" => 201, "response" => 'SUCCESS', 'message' => 'No user exist with this email id. Create your account first then try again.');
            }
            header('Content-Type: application/json');
            echo json_encode($responseData);
        }
    }

    function get_uploaded_resume()
    {
        $data['user_id'] = $this->input->post('user_id');

        $this->form_validation->set_rules('user_id', 'User', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $responseData = array("code" => 201, "response" => 'SUCCESS', 'message' => 'Invalid user.');
        } else {
            $resumeData = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_RESUME__, array('user_id' => $data['user_id']));
            if (!empty($resumeData)) {
                $responseData = array("code" => 200, "response" => 'SUCCESS', 'dataArr' => $resumeData);
            } else {
                $responseData = array("code" => 201, "response" => 'SUCCESS', 'message' => 'No resume found.');
            }
        }
        header('Content-Type: application/json');
        echo json_encode($responseData);
    }

    function get_all_jobs() {
        $dataArr = $this->Common_Model->getCompleteData(__DBC_SCHEMATA_JOBS__);
        if (!empty($dataArr)) {
            $responseData = array("code" => 200, "response" => 'SUCCESS', 'dataArr' => $dataArr);
        } else {
            $responseData = array("code" => 201, "response" => 'SUCCESS', 'message' => 'No jobs found.');
        }
        header('Content-Type: application/json');
        echo json_encode($responseData);
    }

}