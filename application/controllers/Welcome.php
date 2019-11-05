<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		exit("对不起访问路径不正确，请访问正确的页面！");
	}
}
