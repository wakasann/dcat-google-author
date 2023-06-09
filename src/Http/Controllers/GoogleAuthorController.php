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
}