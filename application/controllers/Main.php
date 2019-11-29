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
		$adminJson = '
		{
			"contentManagement": [
				{
					"title": "文章列表",
					"icon": "icon-text",
					"href": "page/news/newsList.html",
					"spread": false
				},
				{
					"title": "分类列表",
					"icon": "icon-text",
					"href": "page/news/typesList.html",
					"spread": false
				},
				{
					"title": "论文审核",
					"icon": "&#xe634;",
					"href": "page/news/newsList.html",
					"spread": false
				}
			],
			"articleManagement":[
				{
					"title":"SCI论文认领",
					"icon": "&#xe609;",
					"href":"page/article/sciArticleList.html",
					"spread":false
				},
				{
					"title":"他引认领",
					"icon": "&#xe609;",
					"href":"page/article/sciArticleList.html",
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
							"href": "page/login/login.html",
							"spread": false,
							"target": "_blank"
						}
					]
				}
			],
			"memberCenter": [
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
			]

		}';

		$userJson = '
		{
			"articleManagement":[
				{
					"title":"SCI论文认领",
					"icon": "&#xe609;",
					"href":"page/article/sciArticleList.html",
					"spread":false
				},
				{
					"title":"他引认领",
					"icon": "&#xe609;",
					"href":"page/article/sciArticleList.html",
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
							"href": "page/login/login.html",
							"spread": false,
							"target": "_blank"
						}
					]
				}
			]
		}';

		if($this->session->identity == 0){
			echo $userJson;
		}else{
			echo $adminJson;
		}
	}
}
