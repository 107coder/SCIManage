<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{

    /**
     * MY_Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $userId = $this->session->job_number;
        // if(empty($userId))
        // {
        //     exit(JsonEcho(4,'请先登录！'));
        // }
    }
}