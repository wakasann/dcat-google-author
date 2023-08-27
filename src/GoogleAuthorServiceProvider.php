<?php

namespace Wakazunn\GoogleAuthor;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Support\Helper;
use Dcat\Admin\Traits\HasFormResponse;
use Dcat\Admin\Widgets\Form as WidgetsForm;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Lang;
use Wakazunn\GoogleAuthor\Http\Controllers\GoogleAuthForm;
use Illuminate\Support\Facades\Cache;
class GoogleAuthorServiceProvider extends ServiceProvider
{
	use HasFormResponse;

	protected $js = [
        'js/index.js',
    ];
	protected $css = [
		'css/index.css',
	];

	protected $menu = [
        [
            'title' => 'Google Authenticator',
            'uri'   => '/wakazunn-gauthenticator',
			'icon'  => 'fa-cogs'
        ],
    ];

	public function register()
	{
		//
	}
    
     public function register_routes(){
        $attributes = [
            'prefix'     => config('admin.route.prefix'),
            'middleware' => config('admin.route.middleware'),
        ];

        app('router')->group($attributes, function ($router) {
            /* @var \Illuminate\Routing\Router $router */
            $router->get('wakazunn-gauthenticator', 'Wakazunn\GoogleAuthor\Http\Controllers\GoogleAuthorController@index');
        });
    }
    
	public function init()
	{
		parent::init();
		$this->register_routes();


		//参考 https://github.com/yexk/dcat-login-google-captcha
		Admin::booting(function () {
			//登录部分的
            $except = admin_base_path('auth/login');
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            // 匹配登陆get
            if (Helper::matchRequestPath('get:' . $except)) {
                $script = '
                ;(function() {
                    var captcha_tpl = \'<fieldset class="form-label-group form-group position-relative has-icon-left">\'
                        captcha_tpl += \'<input id="google_author_code" type="text" style="" class="form-control" name="google_author_code" placeholder="' . static::trans('login.googlecode_placeholder') . '">\'
                        captcha_tpl += \'<div class="form-control-position">\'
                        captcha_tpl += \'<i class="feather icon-image"></i>\'
                        captcha_tpl += \'</div>\'
                        captcha_tpl += \'<label for="google_author_code">' . static::trans('login.googlecode') . '</label>\'
                        captcha_tpl += \'<div class="help-block with-errors"></div>\'
                        captcha_tpl += \'</fieldset>\';
                    	$(captcha_tpl).insertAfter($("#login-form fieldset.form-label-group").get(1));
                })();
                ';
                Admin::script($script);
            }

            // 匹配登陆post
            if (Helper::matchRequestPath('post:' . $except)) {
				
                $username = request()->input('username');
                $captcha = request()->input('google_author_code');

				$user = Administrator::where(['username' => $username])->first();
				if (!$user) {
					return $this->throwHttpResponseException([
                        'username' => $this->getFailedLoginMessage(),
                    ]);
				}
				if (!empty($user->google_auth)) {
					if(empty($captcha)){
						return $this->throwHttpResponseException([
							'google_author_code' => static::trans('login.google_code_required'),
						]);
					}
					$ga = new \PHPGangsta_GoogleAuthenticator();
					$result =  $ga->verifyCode($user->google_auth, $captcha);
					if(!$result){
						return $this->throwHttpResponseException([
							'google_author_code' => static::trans('login.google_code_error'),
						]);
					}
                    Cache::forever('waka_admin_google_author_code_'.$user->id,$user->google_auth);
				}
            }

			

        });
		
	}

	public function settingForm()
	{
		return new Setting($this);
	}

	public function getBindForm(){
		$form = GoogleAuthForm::make();

		$form->text('id');

		return $form;
	}

	/**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('admin.auth_failed')
            ? trans('admin.auth_failed')
            : 'These credentials do not match our records.';
    }

	 /**
     * Throw HttpResponseException.
     *
     * @param array|MessageBag|\Illuminate\Validation\Validator $validationMessages
     */
    protected function throwHttpResponseException($validationMessages)
    {
        throw new HttpResponseException($this->validationErrorsResponse($validationMessages));
    }
}
