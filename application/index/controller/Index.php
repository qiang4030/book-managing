<?php
namespace app\index\controller;

use think\Controller;
use think\facade\Request;
use think\Db;
use think\facade\Session;

class Index extends Controller
{
    public function Index()
    {
        return $this->fetch();
    }

    public function check()
    {
       // $username=$_POST['username'];
        $pwd=$_POST['password'];
        $username=Request::post('username');
        $conn=mysqli_connect('localhost','root','root','library');
        $sql="select * from users where userid='".$username."' and pwd='".$pwd."'";
        $rs=mysqli_query($conn,$sql);
        if($rs!==false)
        {
            if(mysqli_num_rows($rs)>=1)   //用户名和密码是正确
            {
                // session_start();
                //$_SESSION['username']=$username;
                //$_SESSION['userType']='管理员';
                session('username',$username);
                session('pwd',$pwd);
                session('userType','管理员');
                header('location:http://localhost/book-managing/public/index.php/index/index/home');
                exit();
            }
            else  //用户名或密码错误
            {
                echo "<script type='text/javascript'>";
                echo "alert('用户名或密码错误');";
                echo "window.location='http://localhost/book-managing/public/index.php/index/index/index'";
                echo "</script>";
            }
        } 
        else
        {
            echo "<script type='text/javascript'>";
            echo "alert('数据库内部出错');";
            echo "window.location='http://localhost/book-managing/public/index.php/index/index/index'";
            echo "</script>";
        }       
    }

    public function Home()
    {
    	return $this->fetch();
    }

    public function addBook()
    {
        $result = Db::name('bookinfo')->select();
        $book_id = count($result)+1;
        $adddate = date("Y-m-d");
        $this->assign('book_id',$book_id);
        $this->assign('adddate',$adddate);
        return $this->fetch();
    }

    public function addBookInput()
    {
        $data = [];
        $data['id'] = Request::post('bookid');
        $data['book_name'] = Request::post('bookname');
        $data['author'] = Request::post('author');
        $data['book_press'] = Request::post('press');
        $data['book_type'] = Request::post('booktype');
        $data['add_date'] = Request::post('adddate');
        $data['book_brief'] = Request::post('bookbrief');
        $data['book_photo'] = Request::file('photo')
        ->move('./static/uploads',false,'./static/uploads')
        ->getinfo()['name'];

        $result = Db::name('bookinfo')->data($data)->insert();
        if($result !== false)
        {
            return $this->success("图书添加成功！");
        }
        else
        {
            return $this->error("图书添加失败！");
        }
    }

    public function lstBook()
    {
        // $result = Db::table('bookinfo')->paginate(5,false,['query'=>['sltSp'=>$sp]]);
        $result = Db::table('bookinfo')->order('id')->paginate(12,false);
        $this->assign('rs',$result);
        return $this->fetch();
    }

    public function viewBook()
    {
        $book_id = Request::param('bookid');
        // $data = array();
        $book_details = Db::name('bookinfo')
        ->where('id',$book_id)
        ->select();
        $photo = $book_details[0]['book_photo'];
        if(!$photo){
            $photo = 'orig.png';
        }
        $this->assign('photo',$photo);
        $this->assign('details',$book_details);
        return $this->fetch();
    }
    
    public function qryBook()
    {
        $submit = request()->post('submit');
        $str1 = request()->post('book_str1');
        $str2 = request()->post('book_str2');
        $result = Db::table('bookinfo')
        // ->where('book_name','like',"%$str%")
        ->where('book_name','like',"%$str1%")
        ->where('author','like',"%$str2%")
        ->order('id')
        // ->select();
        ->order('id')
        ->paginate(100,false);
        // ->select();
        $this->assign('submit',$submit);
        $this->assign('gjz1',$str1);
        $this->assign('gjz2',$str2);
        $this->assign('rs',$result);
        // foreach ($result as $k => $v) {
        //     echo $v['book_photo'].'<br>';
        // }
        return $this->fetch();
    }

    public function mdfBook()
    {
        $id = Request::param('bookid');
        $booktype = Request::param('booktype');
        if(!isset($id) || $id == "")
        {
            return $this->error("图书不存在！");
        }
        else
        {
            $result = Db::name('bookinfo')->where('id',$id)->find();
            if($result != false)
            {
                $this->assign('bookrs',$result);
                $this->assign('type',$booktype);
                // echo $result['book_type'].strlen($result['book_type']).'<br>'.$booktype.strlen($booktype);
                return $this->fetch();
            }
            else
            {
                return $this->error("没有要修改的图书！");
            }
        }
    }

    public function mdfBookInput()
    {
        $data = [];
        $data['id'] = Request::post('bookid');
        $data['book_name'] = Request::post('bookname');
        $data['author'] = Request::post('author');
        $data['book_press'] = Request::post('press');
        $data['book_type'] = Request::post('booktype','','htmlspecialchars');
        $data['add_date'] = Request::post('adddate');
        $data['book_brief'] = Request::post('bookbrief');
        $data['book_photo'] = Request::file('photo')
        ->move('./static/uploads',false,'./static/uploads')
        ->getinfo()['name'];

        $result = Db::name('bookinfo')->data($data)->update();
        if($result !== false)
        {
            return $this->success("图书修改成功！");
        }
        else
        {
            return $this->error("图书修改失败！");
        }
    }

    public function delBook()
    {
        $bookid = Request::param('bookid');
        $result = Db::table('bookinfo')->order('id')->select();
        // print_r($result);
        $i=1;
        $result = Db::name('bookinfo')->where('id',$bookid)->delete();
        if($result !== false)
        {
            $this->success('图书删除成功！');
        }
        else
        {
            $this->error('图书删除失败！');
        }
        foreach($result as $k=>$v)
        {
            $newarr = $v;
            // print_r($v);
            // echo count($result);
            // echo $i.' ';
            echo $v['id'].' ';
            Db::name('bookinfo')->where('id',$v['id'])->setField('id',$i);
            // echo $i.' ';
            $i++;
        }
        // echo count($result);
        // print_r($result[0]['id']);
    }

    public function srtBook()
    {
        $booktype = Request::param('lable');
        $rs = Db::name('bookinfo')
        ->where('book_type','like',"%$booktype%")
        ->order('id')
        ->paginate(8,true);
        $photo = 'orig.png';
        $this->assign('photo',$photo);
        $this->assign('rs',$rs);
        return $this->fetch();
    }
}
