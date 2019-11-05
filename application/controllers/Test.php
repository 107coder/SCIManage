<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

	public function index()
	{
        $json = '{
            "title": "图片管理",
            "id": "Images",
            "start": 0,
            "data": [
                {
                    "src": "images/userface1.jpg",
                    "thumb": "images/userface1.jpg",
                    "alt": "美女生活照1",
                    "pid":"1"
                },
                {
                    "src": "images/userface2.jpg",
                    "thumb": "images/userface2.jpg",
                    "alt": "美女生活照2",
                    "pid":"2"
                },
                {
                    "src": "images/userface3.jpg",
                    "thumb": "images/userface3.jpg",
                    "alt": "美女生活照3",
                    "pid":"3"
                },
                {
                    "src": "images/userface4.jpg",
                    "thumb": "images/userface4.jpg",
                    "alt": "美女生活照4",
                    "pid":"4"
                },
                {
                    "src": "images/userface5.jpg",
                    "thumb": "images/userface5.jpg",
                    "alt": "美女生活照5",
                    "pid":"5"
                },
               
            ]
        }';

        echo $json;
	}
}
