<?php
namespace app\index\controller;

use think\Controller;
use think\facade\Request;
use think\Db;
use think\facade\Session;

class User extends Controller
{
	public function index()
	{
		return $this->fetch();
	}
	public function signup()
	{
		return $this->fetch();
	}
	public function signupoload()
	{
		$data = [];
		$username = Request::post('username');
		$password = Request::post('password');
		$data = ['userid'=>$username,'pwd'=>$password];

		$rs1 = Db::name('users')->where('userid','=',$username)->select();
		if(count($rs1)>0)
		{
			$this->error('该用户名已被占用！');
		}
		else
		{
			$rs2 = Db::name('users')->insert($data);
			if($rs2)
			{
				$this->success('恭喜您，注册成功！','Index/index');
			}
			else
			{
				$this->error('注册失败！');
			}
		}
	}

	public function delUser()
	{
		$userid = Request::param('username');
		echo $userid;
	}
	public function userinfo()
    {
        $rs = Db::name('users')->select();
        $userid = Request::param('userid');
        $this->assign('userid',$userid);
        $this->assign('rs',$rs);
        return $this->fetch();
    }
    public function mdfUser()
    {
    	$username = Request::param('userid');
        $result = Db::name('users')->where('userid',$username)->find();
        if($result != false)
        {
            $this->assign('userrs',$result);
            $this->assign('userid',$username);
            // echo $result['book_type'].strlen($result['book_type']).'<br>'.$booktype.strlen($booktype);
            return $this->fetch();
        }
        else
        {
            return $this->error("没有要修改的用户！");
        }
    }
    public function mdfUserInput()
    {
    	$data = [];
        $data['userid'] = Request::post('userid');
        $data['pwd'] = Request::post('pwd');
        $rs1 = Db::name('users')->where('pwd',$data['pwd'])->find();
        if($rs1)
        {
        	return $this->error('新密码与原密码相同，修改失败！');
        }
		$result = Db::name('users')->data($data)->update();
        if($result != false)
        {
            return $this->success("用户修改成功！");
        }
        else
        {
            return $this->error("用户修改失败！");
        }
    }

	public function logout()
	{
		Session::clear();
		return $this->success('退出成功，期待您的再次光临！','Index/index');
	}
}