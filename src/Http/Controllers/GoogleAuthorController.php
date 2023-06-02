<?php

namespace Wakazunn\GoogleAuthor\Http\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Card;
use Illuminate\Routing\Controller;

use Dcat\Admin\Http\Controllers\AuthController as BaseAuthController;
use Dcat\Admin\Models\Administrator;
use Wakazunn\GoogleAuthor\GoogleAuthorServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoogleAuthorController extends BaseAuthController
{
    public function index(Content $content)
    {
        return $content
            ->title('Google验证')
            ->description('Google验证器')
            ->body(new Card(new GoogleAuthForm()));
    }

     /**
     * Show the login page.
     *
     * @return Content|\Illuminate\Http\RedirectResponse
     */
    public function getLogin1(Content $content)
    {
        if ($this->guard()->check()) {
            return redirect($this->getRedirectPath());
        }
        $googlecodeLabel = GoogleAuthorServiceProvider::trans('login.googlecode');
        $googlecodePlaceholder = GoogleAuthorServiceProvider::trans('login.googlecode_placeholder');
        
        return $content->full()->body( Admin::view('wakazunn.google-author::login',[
            'google_code_label' => $googlecodeLabel,
            'google_code_placeholder' => $googlecodePlaceholder
        ]));
    }

    /**
     * Handle a login request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function postLogin1(Request $request)
    {
        $credentials = $request->only([$this->username(), 'password']);
        $remember = (bool) $request->input('remember', false);

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($credentials, [
            $this->username()   => 'required',
            'password'          => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorsResponse($validator);
        }

        $googleCode = $request->post('googlecode',null);

        if(!$this->guard()->validate($credentials)){
            return $this->validationErrorsResponse([
                $this->username() => $this->getFailedLoginMessage(),
            ]);
        }

        $userInfo = Administrator::query()->where($this->username(),$credentials[$this->username()])->first();
        if($userInfo && !empty($userInfo->google_auth)){
            if(empty($googleCode) || strlen($googleCode) != 6){
                return $this->validationErrorsResponse([
                    'googlecode' => GoogleAuthorServiceProvider::trans('login.google_code_required'),
                ]);
            }
            $ga = new \PHPGangsta_GoogleAuthenticator();
            $checkResult = $ga->verifyCode($userInfo->google_auth, $googleCode, 2);    // 2 = 2*30sec clock tolerance
            if(!$checkResult){
                return $this->validationErrorsResponse([
                    'googlecode' => GoogleAuthorServiceProvider::trans('login.google_code_error'), 
                ]);
            }
        }

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }

        return $this->validationErrorsResponse([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }

}