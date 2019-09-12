<?php

namespace app\index\controller;
use think\Controller;
class Hook extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
}