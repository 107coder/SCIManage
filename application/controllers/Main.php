<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends MY_Controller {

	public function index()
	{
		$this->load->view('main.html');
	}

	public function modify()
	{
		$this->load->view('modify.html');
	}

	public function nav(){
		if(isset($this->session->identity)){
			$paperReviewUrl = $this->session->identity==2?"paperReviewList_root.html":"paperReviewList_admin.html";
		}
		$contentManagement = '"contentManagement": [
			{
				"title": "分类列表",
				"icon": "icon-text",
				"href": "page/admin/typesList.html",
				"spread": false
			},
			{
				"title": "SCI论文列表",
				"icon": "icon-text",
				"href": "page/admin/sciArticleList.html",
				"spread": false
			},
			{
				"title": "他引论文列表",
				"icon": "icon-text",
				"href": "page/admin/citationList.html",
				"spread": false
			},
			{
				"title": "SCI论文审核",
				"icon": "&#xe634;",
				"href": "page/admin/'.$paperReviewUrl.'",
				"spread": false
			}
		]';
		$articleManagement = '"articleManagement":[
			{
				"title":"SCI论文认领",
				"icon": "&#xe609;",
				"href":"page/article/sciArticleList.html",
				"spread":false
			},
			{
				"title":"他引认领",
				"icon": "&#xe609;",
				"href":"page/citation/citationList.html",
				"spread":false
			},
			{
				"title":"我的论文",
				"icon": "&#xe609;",
				"href":"",
				"spread":false,
				"children": [
					{
						"title": "SCI论文",
						"icon": "&#xe61c;",
						"href": "page/article/mySciArticleList.html",
						"spread": false
					},
					{
						"title": "他引论文",
						"icon": "&#xe609;",
						"href": "page/citation/myCitationList.html",
						"spread": false,
						"target": ""
					}
				]
			}
		]';
		$memberCenter = '"memberCenter": [
			{
				"title": "教师用户",
				"icon": "&#xe612;",
				"href": "page/user/teacher/teacherList.html",
				"spread": false
			},
			{
				"title": "学生用户",
				"icon": "&#xe612;",
				"href": "page/user/student/studentList.html",
				"spread": false
			}
		]';
		$adminManagement = '"contentManagement": [
			{
				"title": "教师用户",
				"icon": "&#xe612;",
				"href": "page/user/teacher/teacherList.html",
				"spread": false
			},
			{
				"title": "论文审核",
				"icon": "&#xe634;",
				"href": "page/admin/'.$paperReviewUrl.'",
				"spread": false
			}
		]';
		// 校级管理员
		$rootJson = "{".$contentManagement.','.$articleManagement.','.$memberCenter."}";
	    // 院级管理员
		$adminJson = "{".$articleManagement.','.$adminManagement."}";
		// 普通人员
		$userJson = "{".$articleManagement."}";

		if($this->session->identity == 0){
			echo $userJson;	
		}else if($this->session->identity == 2){
			echo $rootJson;
		}else {
			echo $adminJson;
		}
	}
}
