<?php

namespace GoogleAuthor;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;

class GoogleAuthorServiceProvider extends ServiceProvider
{
	protected $js = [
        'js/index.js',
    ];
	protected $css = [
		'css/index.css',
	];

	protected $menu = [
        [
            'title' => 'Google Authenticator',
            'uri'   => '/google-author',
			'icon'  => 'fa-cogs'
        ],
    ];

	public function register()
	{
		//
	}

	public function init()
	{
		parent::init();

		//
		
	}

	public function settingForm()
	{
		return new Setting($this);
	}
}
