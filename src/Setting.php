<?php

namespace Wakazunn\GoogleAuthor;

use Dcat\Admin\Extend\Setting as Form;

class Setting extends Form
{
    public function form()
    {
        $this->text('show_name','扫码之后显示的名称')->default('管理后台')->required();
    }
}
