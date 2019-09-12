<?php
namespace Home\Controller;
use think\Controller;
use think\Hook;//引进促发钩子行为的类，是为下面的Hook::add()调用做伏笔的
class Index extends Controller
{
    public function login(){
        //这里我设置一个ad行为的标签，也就是给我自定义的adBehavior钩子行为类添加一个促发行为的标识
        Hook::add('ad','Behavior\\adBehavior');
        //第一个是执行标签的名称，第二个参数是行为的类的地址
        Hook::add('test', "Home\\Behaviors\\testBehavior");
        //Hook::add('test2', "Home\\Behaviors\\testBehavior");
        $param=array('name'=>'testBehavior');
        $param2=array('LIS'=>'LLISTION');
        $param3=array('music'=>'cangjingshikong');
        $this->assign('param',$param);
        $this->assign('param',$param2);
        $this->assign('param',$param3);
        $this->display();
    }
    public function indexs()
    {
        echo 111;die;
    }
}