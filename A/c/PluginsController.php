<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/08
// +----------------------------------------------------------------------


namespace A\c;

use FrPHP\lib\Controller;
use FrPHP\Extend\Page;
class PluginsController extends CommonController
{
	
	public function index(){
		//检查更新链接是否可以访问
		$api = 'http://api.jizhicms.cn/plugins.php';
		$ch = curl_init();
		$timeout = 3;
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch,CURLOPT_URL,$api);
		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpcode==200){
			$isok = true;
			$res1 = json_decode($res,1);
			if($res1['code']!=0){
				$isok = false;
			}else{
				$allplugins = $res1['data'];
			}
		}else{
			$isok = false;
		}
		$this->title = $this->frparam('title',1);
		if($this->frparam('isdown')){
			$isdown = $this->frparam('isdown');
			switch($isdown){
				case 1:
					
					//已安装
					$page = new Page('plugins');
					$sql = '1=1';
					$data = $page->where($sql)->orderby('addtime desc')->limit($this->frparam('limit',0,10))->page($this->frparam('page',0,1))->go();
					$pages = $page->pageList();
					$dir = APP_PATH.APP_HOME.'/exts';
					foreach($data as $k=>$v){
						//已下载该插件
						$config = require_once($dir.'/'.$v['filepath'].'/config.php');
						$data[$k]['isinstall'] = true;
						if($isok && array_key_exists($v['filepath'],$allplugins) && version_compare($config['version'],$allplugins[$v['filepath']]['version'],'<')){
							//有更新
							$data[$k]['isupdate'] = true;
							$data[$k]['official'] = $allplugins[$v['filepath']]['official'];
						}else{
							//无更新
							$data[$k]['isupdate'] = false;
							$data[$k]['official'] =  array_key_exists($v['filepath'],$allplugins) ? $allplugins[$v['filepath']]['official'] : 0;
						}
						$data[$k]['exists'] = true;
					
						
					}
					
					
					$this->pages = $pages;
					$this->lists = $data;
					$this->sum = $page->sum;
					$this->listpage = $page->listpage;//分页数组-自定义分页可用
					$this->prevpage = $page->listpage['prev'];//上一页
					$this->nextpage = $page->listpage['next'];//下一页
					$this->allpage = $page->listpage['allpage'];//总页数
					$this->display('plugins-list');
					exit;
				break;
				case 2:
				//未安装
					$dir = APP_PATH.APP_HOME.'/exts';
					$fileArray=array();
					if (false != ($handle = opendir ( $dir ))) {
						while ( false !== ($file = readdir ( $handle )) ) {
							//去掉"“.”、“..”以及带“.xxx”后缀的文件
							if ($file != "." && $file != ".."  && strpos($file,'.')===false) {
								//检查是否安装
								if(!M('plugins')->find(['filepath'=>$file])){
									$fileArray[]=$file;
								}
								
								
							}
						}
						//关闭句柄
						closedir ( $handle );
					}
					$arraypage = new \ArrayPage($fileArray);
					$data = $arraypage->query(['page'=>$this->frparam('page',0,1),'title'=>$this->title,'isdown'=>$this->frparam('isdown')])->setPage(['limit'=>$this->frparam('limit',0,10)])->go();
					
					foreach($data as $k=>$v){
						//已下载该插件
						$config = require_once($dir.'/'.$v.'/config.php');
						$data[$k] = ['name'=>$config['name'],'filepath'=>$v,'description'=>$config['desc'],'version'=>$config['version'],'author'=>$config['author'],'update_time'=>strtotime($config['update_time']),'module'=>$config['module'],'isopen'=>0,'config'=>'','isinstall'=>false];
						
						if($isok && array_key_exists($v,$allplugins) && version_compare($config['version'],$allplugins[$v]['version'],'<')){
							//有更新
							$data[$k]['isupdate'] = true;
							$data[$k]['official'] = $allplugins[$v]['official'];
						}else{
							//无更新
							$data[$k]['isupdate'] = false;
							$data[$k]['official'] =  array_key_exists($v,$allplugins) ? $allplugins[$v]['official'] : 0;
						}
						$data[$k]['exists'] = true;
					
						
					}
					
					//var_dump($data);
					
					$this->pages = $arraypage->pageList();
					$this->sum = $arraypage->sum;//总数据
					$this->listpage = $arraypage->listpage;//分页数组-自定义分页可用
					$this->prevpage = $arraypage->prevpage;//上一页
					$this->nextpage = $arraypage->nextpage;//下一页
					$this->allpage = $arraypage->allpage;//总页数
					$this->lists = $data;
					$this->display('plugins-list');
					exit;
					
					
				break;
				case 3:
				//未下载
					$dir = APP_PATH.APP_HOME.'/exts';
					$fileArray=array();
					if (false != ($handle = opendir ( $dir ))) {
						while ( false !== ($file = readdir ( $handle )) ) {
							//去掉"“.”、“..”以及带“.xxx”后缀的文件
							if ($file != "." && $file != ".."  && strpos($file,'.')===false) {
								$fileArray[]=$file;
								
							}
						}
						//关闭句柄
						closedir ( $handle );
					}
					
					$lists = [];
					
					if($isok){
						foreach($allplugins as $k=>$v){
							if(!in_array($k,$fileArray)){
								
								$lists[$k] = $v;
								$lists[$k]['exists'] = false;
								$lists[$k]['description'] = $v['desc'];
								$lists[$k]['isinstall'] = false;
								$lists[$k]['isupdate'] = false;
								$lists[$k]['official'] = 1;
								
							}
							
				
						}
						
						$arraypage = new \ArrayPage($lists);
						$data = $arraypage->query(['page'=>$this->frparam('page',0,1),'title'=>$this->title,'isdown'=>$this->frparam('isdown')])->setPage(['limit'=>$this->frparam('limit',0,10)])->go();
						$this->pages = $arraypage->pageList();
						$this->sum = $arraypage->sum;//总数据
						$this->listpage = $arraypage->listpage;//分页数组-自定义分页可用
						$this->prevpage = $arraypage->prevpage;//上一页
						$this->nextpage = $arraypage->nextpage;//下一页
						$this->allpage = $arraypage->allpage;//总页数
						$this->lists = $data;
						
						
					}else{
						$this->pages = '';
						$this->sum = 0;//总数据
						$this->listpage = false;//分页数组-自定义分页可用
						$this->prevpage = false;//上一页
						$this->nextpage = false;//下一页
						$this->allpage = 0;//总页数
						$this->lists = [];
						
					}
					$this->display('plugins-list');
					exit;
				
				break;
			}
		
		
		}
		
		
		$dir = APP_PATH.'A/exts';
		$fileArray=array();
		if (false !== ($handle = opendir ( $dir ))) {
			while ( false !== ($file = readdir ( $handle )) ) {
				//去掉"“.”、“..”以及带“.xxx”后缀的文件
				if ($file != "." && $file != ".."  && strpos($file,'.')===false) {
					$fileArray[]=$file;
					
				}
			}
			//关闭句柄
			closedir ( $handle );
		}
		$pls = M('plugins')->findAll();
		$plugins = [];
		foreach($pls as $k=>$v){
			$plugins[$v['filepath']] = $v;
		}
		$lists = [];
		
		if($isok){
			foreach($allplugins as $k=>$v){
				if(in_array($k,$fileArray)){
					//已下载该插件
					$config = require_once($dir.'/'.$k.'/config.php');
					if(isset($plugins[$k])){
						$lists[$k] = $plugins[$k];
						$lists[$k]['isinstall'] = true;
						
					}else{
						$lists[$k] = ['name'=>$config['name'],'filepath'=>$k,'description'=>$config['desc'],'version'=>$config['version'],'author'=>$config['author'],'update_time'=>strtotime($config['update_time']),'module'=>$config['module'],'isopen'=>0,'config'=>'','isinstall'=>false];
					}
					if(version_compare($config['version'],$v['version'],'<')){
						//有更新
						$lists[$k]['isupdate'] = true;
					}else{
						//无更新
						$lists[$k]['isupdate'] = false;
					}
					$lists[$k]['exists'] = true;
					$lists[$k]['official'] = $v['official'];
					
				}else{
					$lists[$k] = $v;
					$lists[$k]['exists'] = false;
					$lists[$k]['description'] = $v['desc'];
					$lists[$k]['isinstall'] = false;
					$lists[$k]['isupdate'] = false;
					$lists[$k]['official'] = $v['official'];
					
				}
				
				
			}
			
			//检查软件是否还有自定义软件未加入进去
			foreach($fileArray as $v){
				if(!isset($lists[$v])){
					//已下载该插件
					$config = require_once($dir.'/'.$v.'/config.php');
					if(isset($plugins[$v])){
						$lists[$v] = $plugins[$v];
						$lists[$v]['isinstall'] = true;
					}else{
						$lists[$v] = ['name'=>$config['name'],'filepath'=>$v,'description'=>$config['desc'],'version'=>$config['version'],'author'=>$config['author'],'update_time'=>strtotime($config['update_time']),'module'=>$config['module'],'isopen'=>0,'config'=>'','isinstall'=>false];
					}
					$lists[$v]['isupdate'] = false;
					$lists[$v]['exists'] = true;
					$lists[$v]['official'] = 0;
				}
			}
			
			
		}else{
			
			foreach($fileArray as $k=>$v){

				//检查是否安装
				if(strpos($v,'.')===false){
					$config = require_once($dir.'/'.$v.'/config.php');
					if(isset($plugins[$v])){
						$lists[$k] = $plugins[$v];
						$lists[$k]['isinstall'] = true;
					}else{
						$lists[] = ['name'=>$config['name'],'filepath'=>$v,'description'=>$config['desc'],'version'=>$config['version'],'author'=>$config['author'],'update_time'=>strtotime($config['update_time']),'module'=>$config['module'],'isopen'=>0,'config'=>'','isinstall'=>false];
					}
					$lists[$k]['isupdate'] = false;
					$lists[$k]['exists'] = true;
					$lists[$k]['official'] = 2;//本地
					
				}
				
				
				
			}
			
		}
		if($this->title){
			$newlist = [];
			foreach($lists as $v){
				if(strpos($v['name'],$this->title)!==false){
					$newlist[]=$v;
				}
				
			}
			$lists  = $newlist;
		}
		
		$arraypage = new \ArrayPage($lists);
		$data = $arraypage->query(['page'=>$this->frparam('page',0,1),'title'=>$this->title,'isdown'=>$this->frparam('isdown')])->setPage(['limit'=>$this->frparam('limit',0,10),'page'=>$this->frparam('page',0,1)])->go();
		$this->pages = $arraypage->pageList();
		$this->sum = $arraypage->sum;//总数据
		$this->listpage = $arraypage->listpage;//分页数组-自定义分页可用
		$this->prevpage = $arraypage->prevpage;//上一页
		$this->nextpage = $arraypage->nextpage;//下一页
		$this->allpage = $arraypage->allpage;//总页数
		$this->lists = $data;
		
		$this->display('plugins-list');
		
		
	}
	
	//更改软件状态
	function change_status(){
		$filepath = $this->frparam('filepath',1);
		if($filepath){
			$plugins = M('plugins')->find(['filepath'=>$filepath]);
			if($plugins['isopen']==1){
				$isopen = 0;
			}else{
				$isopen = 1;
			}
			M('plugins')->update(['id'=>$plugins['id']],['isopen'=>$isopen]);
			
			//检测插件是否注册hook
			if(M('hook')->find(['plugins_name'=>$filepath])){
				M('hook')->update(['plugins_name'=>$filepath],['isopen'=>$isopen]);
			}
			setCache('hook',null);
			JsonReturn(array('code'=>0,'msg'=>'success'));
		}
		JsonReturn(array('code'=>1,'msg'=>'参数错误！'));
		
		
	}
	
	//打包下载
	function output(){
		$filepath = $this->frparam('filepath',1);
		if(!$filepath){
			Error('链接错误！');
		}
		$zip = new \ZipArchive();

		if ($zip->open($filepath.'.zip', \ZipArchive::CREATE|\ZipArchive::OVERWRITE) === TRUE) {
			$this->addFileToZip(APP_PATH.'A/exts/'.$filepath.'/', $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
			$zip->close(); //关闭处理的zip文件
			
			$zip = $filepath.'.zip';
			$zipname = date('YmdHis');
			//打开文件
			$file = fopen($zip,"r");
			//返回的文件类型
			Header("Content-type: application/octet-stream");
			//按照字节大小返回
			Header("Accept-Ranges: bytes");
			//返回文件的大小
			Header("Accept-Length: ".filesize($zip));
			//这里对客户端的弹出对话框，对应的文件名
			Header("Content-Disposition: attachment; filename=".$zip);
			//修改之前，一次性将数据传输给客户端
			echo fread($file, filesize($zip));
			//修改之后，一次只传输1024个字节的数据给客户端
			//向客户端回送数据
			$buffer=1024;//
			//判断文件是否读完
			while (!feof($file)) {
				//将文件读入内存
				$file_data=fread($file,$buffer);
				//每次向客户端回送1024个字节的数据
				echo $file_data;
			}
			
			fclose($file);
			
		}else{
			exit('无法打开文件，或者文件创建失败');
		}
	}
	
	function addFileToZip($path, $zip) {
		$handler = opendir($path); //打开当前文件夹由$path指定。
		/*
		循环的读取文件夹下的所有文件和文件夹
		其中$filename = readdir($handler)是每次循环的时候将读取的文件名赋值给$filename，
		为了不陷于死循环，所以还要让$filename !== false。
		一定要用!==，因为如果某个文件名如果叫'0'，或者某些被系统认为是代表false，用!=就会停止循环
		*/
		while (($filename = readdir($handler)) !== false) {
			if ($filename != "." && $filename != "..") {//文件夹文件名字为'.'和‘..’，不要对他们进行操作
				if (is_dir($path . "/" . $filename)) {// 如果读取的某个对象是文件夹，则递归
					$this->addFileToZip($path . "/" . $filename, $zip);
				} else { //将文件加入zip对象
					$zip->addFile($path . "/" . $filename);
				}

			}
		}
		@closedir($path);
	}

	
	//安装下载
	function action_do(){
		$filepath = $this->frparam('path',1);
		$type = $this->frparam('type');
		$dir = APP_PATH.APP_HOME.'/exts';
		if($filepath){
			if($type==1){
				//安装
				//执行插件控制器安装程序
				require_once($dir.'/'.$filepath.'/PluginsController.php');
				$plg = new \A\exts\PluginsController($this->frparam());
				
				
				$step1 = $plg->install();//执行安装
				if(!$step1){
					JsonReturn(array('code'=>1,'msg'=>'执行插件安装程序失败！'));
				}

				$config = require_once($dir.'/'.$filepath.'/config.php');
				$w = ['name'=>$config['name'],'filepath'=>$filepath,'description'=>$config['desc'],'version'=>$config['version'],'author'=>$config['author'],'update_time'=>strtotime($config['update_time']),'module'=>$config['module'],'isopen'=>0,'config'=>'','addtime'=>time()];
				if(M('plugins')->find(['filepath'=>$w['filepath']])){
					JsonReturn(array('code'=>1,'msg'=>'插件已安装，请勿重复操作！'));
				}
				//复制文件到对应文件夹
				//移动前台插件控制器
				$sourcefile = $dir.'/'.$filepath.'/controller/home';
				$target = APP_PATH.'Home/plugins';
				if(!is_dir($target)){
					mkdir($target,0777);
				}
				if(is_dir($sourcefile) && is_dir($target)){
					if (false != ($handle = opendir ( $sourcefile ))) {
					
						while ( false !== ($file = readdir ( $handle )) ) {
							//去掉"“.”、“..”以及带“.xxx”后缀的文件
							if ($file != "." && $file != "..") {
								$fs = $sourcefile.'/'.$file;
								$ft = $target.'/'.$file;
								$r = $this->file2dir($fs,$ft);
								if(!$r){
									JsonReturn(array('code'=>1,'msg'=>'插件安装失败！sourcefile:'.$fs.' targetfile:'.$ft));
								}
							}
						}
						//关闭句柄
						closedir ( $handle );
					}
					
				}
				
				//移动后台插件控制器
				$sourcefile = $dir.'/'.$filepath.'/controller/admin';
				$target = APP_PATH.'A/plugins';
				if(!is_dir($target)){
					mkdir($target,0777);
				}
				if(is_dir($sourcefile) && is_dir($target)){
					if (false != ($handle = opendir ( $sourcefile ))) {
						
						while ( false !== ($file = readdir ( $handle )) ) {
							//去掉"“.”、“..”以及带“.xxx”后缀的文件
							if ($file != "." && $file != "..") {
								$fs = $sourcefile.'/'.$file;
								$ft = $target.'/'.$file;
								$r = $this->file2dir($fs,$ft);
								if(!$r){
									JsonReturn(array('code'=>1,'msg'=>'插件安装失败！sourcefile:'.$fs.' targetfile:'.$ft));
								}
							}
						}
						//关闭句柄
						closedir ( $handle );
					}
				
				}
				
				
				//移动扩展类文件
				$src = $dir.'/'.$filepath.'/class';
				$dst = APP_PATH.'FrPHP/Extend';
				if(is_dir($src)){
					$this->recurse_copy($src,$dst);
				}
				
				$res = M('plugins')->add($w);
				
				setCache('hook',null);
				
				
				JsonReturn(array('code'=>0,'msg'=>'安装成功！'));
			}else{
				//卸载
				if(stripos($filepath,'/')!==false){
					JsonReturn(array('code'=>1,'msg'=>'非法操作！'));
				}
				$config = require_once($dir.'/'.$filepath.'/config.php');
				$w = ['filepath'=>$filepath];
				$plugins = M('plugins')->find($w);
				if(!$plugins){
					if($type==2){
						//批量删除文件
						if(is_dir(APP_PATH.'A/exts/'.$filepath)){
							deldir(APP_PATH.'A/exts/'.$filepath);
						}
						JsonReturn(array('code'=>0,'msg'=>'删除成功！'));
				
					}
					
					JsonReturn(array('code'=>1,'msg'=>'插件已移除，请勿重复操作！'));
				}
				//移除文件夹
				$target = APP_PATH.'Home/plugins';
				$sourcefile = $dir.'/'.$filepath.'/controller/home';
				if(is_dir($sourcefile) && is_dir($target)){
					if (false != ($handle = opendir ( $sourcefile ))) {
						
						while ( false !== ($file = readdir ( $handle )) ) {
							//去掉"“.”、“..”以及带“.xxx”后缀的文件
							if ($file != "." && $file != "..") {
								$ft = $target.'/'.$file;
								if(file_exists($ft)){
									unlink($ft);
								}
								
							}
						}
						//关闭句柄
						closedir ( $handle );
					}
				}
				$target = APP_PATH.'A/plugins';
				$sourcefile = $dir.'/'.$filepath.'/controller/admin';
				if(is_dir($sourcefile) && is_dir($target)){
					if (false != ($handle = opendir ( $sourcefile ))) {
						
						while ( false !== ($file = readdir ( $handle )) ) {
							//去掉"“.”、“..”以及带“.xxx”后缀的文件
							if ($file != "." && $file != "..") {
								$ft = $target.'/'.$file;
								if(file_exists($ft)){
									unlink($ft);
								}
								
							}
						}
						//关闭句柄
						closedir ( $handle );
					}
				}
				//移动扩展类文件
				$src = $dir.'/'.$filepath.'/class';
				$dst = APP_PATH.'FrPHP/Extend';
				if(is_dir($src)){
					$this->recurse_copy($src,$dst);
				}
				
				
				
				
				//执行插件控制器卸载程序
				require_once($dir.'/'.$filepath.'/PluginsController.php');
				$plg = new \A\exts\PluginsController($this->frparam());
				
				$step1 = $plg->uninstall();//执行安装
				if(!$step1){
					JsonReturn(array('code'=>1,'msg'=>'执行插件卸载程序失败！'));
				}
				$res = M('plugins')->delete(['id'=>$plugins['id']]);
				
				setCache('hook',null);
				if($type==2){
					//批量删除文件
					if(is_dir(APP_PATH.'A/exts/'.$filepath)){
						deldir(APP_PATH.'A/exts/'.$filepath);
					}
					JsonReturn(array('code'=>0,'msg'=>'删除成功！'));
			
				}
				
				
				
				JsonReturn(array('code'=>0,'msg'=>'卸载成功！'));
				
				
			}
			
			
		}
		JsonReturn(array('code'=>1,'msg'=>'参数错误！'));
	}

	//复制图片  file2dir("01/5.jpg", "01/successImg/a.jpg");
	function file2dir($sourcefile, $filename){
		 if( !file_exists($sourcefile)){
			 return false;
		 }
		 //$filename = basename($sourcefile);
		 return copy($sourcefile,  $filename);
	}
	// 原目录，复制到的目录
	function recurse_copy($src,$dst) {  
	 
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					$this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}

	
	//配置信息
	function setconf(){
		$id = $this->frparam('id');
		$plugins = M('plugins')->find(['id'=>$id]);
		if($id && $plugins){
			//忽略Notice报错
			error_reporting(E_ALL^E_NOTICE);
			
			//执行插件控制器卸载程序
			$dir = APP_PATH.APP_HOME.'/exts';
			require_once($dir.'/'.$plugins['filepath'].'/PluginsController.php');
			$plg = new \A\exts\PluginsController($this->frparam());
			//转入插件内部处理
			if($_POST){
				
				$plg->setconfigdata($this->frparam());//传入插件内部处理
				exit;
				
			}
			
			
			$plg->setconf($plugins);
			exit;
		}
		JsonReturn(array('code'=>1,'msg'=>'参数错误,必须携带插件ID！'));
		
		//Error('参数错误！');
		
	}
	
	//安装说明
	function desc(){
		$filepath = $this->frparam('filepath',1);
		
		if($filepath){
			//忽略Notice报错
			error_reporting(E_ALL^E_NOTICE);
			
			//执行插件控制器卸载程序
			$dir = APP_PATH.APP_HOME.'/exts';
			require_once($dir.'/'.$filepath.'/PluginsController.php');
			$plg = new \A\exts\PluginsController($this->frparam());
			
			$plg->desc();
			exit;
		}
		JsonReturn(array('code'=>1,'msg'=>'参数错误,必须携带插件ID！'));
	}
	
	//更新插件
	function update(){
		$filepath = $this->frparam('filepath',1);
		if($filepath){
			if($this->frparam('action',1)){
				$action = $this->frparam('action',1);
				// 自己获取这些信息
				$remote_url  = urldecode($this->frparam('download_url',1));
				$file_size   = $this->frparam('filesize',1);
				$tmp_path    = Cache_Path."/update_".$filepath.".zip";//临时下载文件路径
				switch ($action) {
				    case 'prepare-download':
				    	$code = 0;
						ob_start(); 
						$ch=curl_init($remote_url); 
						curl_setopt($ch,CURLOPT_HEADER,1); 
						curl_setopt($ch,CURLOPT_NOBODY,1); 
						$okay=curl_exec($ch); 
						curl_close($ch); 
						$head=ob_get_contents(); 
						ob_end_clean(); 
						$regex='/Content-Length:\s([0-9].+?)\s/'; 
						$count=preg_match($regex,$head,$matches); 
						$filesize = isset($matches[1])&&is_numeric($matches[1])?$matches[1]:0; 

				        JsonReturn(array('code'=>0,'size'=>$filesize));
				        break;
				    case 'start-download':
				        // 这里检测下 tmp_path 是否存在
				        try {
				            set_time_limit(0);
				            touch($tmp_path);
				            // 做些日志处理
				            if ($fp = fopen($remote_url, "rb")) {
				                if (!$download_fp = fopen($tmp_path, "wb")) {
				                    exit;
				                }
				                while (!feof($fp)) {
				                    if (!file_exists($tmp_path)) {
				                        // 如果临时文件被删除就取消下载
				                        fclose($download_fp);
				                        exit;
				                    }
				                    fwrite($download_fp, fread($fp, 1024 * 8 ), 1024 * 8);
				                }
				                fclose($download_fp);
				                fclose($fp);
				            } else {
				                exit;
				            }
				        } catch (Exception $e) {
				            Storage::remove($tmp_path);
				            JsonReturn(['code'=>1,'msg'=>'发生错误：'.$e->getMessage()]);
				        }

				        JsonReturn(['code'=>0,'tmp_path'=>$tmp_path]);
				        break;
				    case 'get-file-size':
				        // 这里检测下 tmp_path 是否存在
				        if (file_exists($tmp_path)) {
				            
				            JsonReturn(['code'=>0,'size'=>filesize($tmp_path)]);
				        }
				        break;
				    case 'file-upzip':

				    	if (!file_exists($tmp_path)) {//先判断待解压的文件是否存在
						   JsonReturn(['code'=>1,'msg'=>'下载缓存文件不存在！']);
						}
						$path = APP_PATH.'A/exts/';
						$zip = new \ZipArchive;
						//$tmp_path = str_replace('/','\\',$tmp_path);
						$res = $zip->open($tmp_path);
						if ($res === TRUE) {
							
							//解压缩到test文件夹
							$zip->extractTo(APP_PATH.'A/exts');
							$zip->close();
						} else {
							
							JsonReturn(['code'=>1,'msg'=>'解压失败：failed, code:' . $res]);
						}
					
						if($filepath=='jizhicmsupdate'){
							$isinstall = true;
						}else{
							if(M('plugins')->find(['filepath'=>$filepath])){
								$isinstall = true;
							}else{
								$isinstall = false;
							}
						}
						
						JsonReturn(['code'=>0,'msg'=>'解压完成！','isinstall'=>$isinstall]);
				    	break;
				    case 'plugin-install':
				    	$dir = APP_PATH.'A/exts';
				    	require_once($dir.'/'.$filepath.'/PluginsController.php');
						$plg = new \A\exts\PluginsController($this->frparam());
						
						
						$step1 = $plg->install();//执行安装
						if(!$step1){
							JsonReturn(array('code'=>1,'msg'=>'执行插件安装程序失败！'));
						}

						$config = require_once($dir.'/'.$filepath.'/config.php');
						
						$plg_old = M('plugins')->find(['filepath'=>$filepath]);
						if($plg_old){
							//保存原配置
							$w = ['name'=>$config['name'],'filepath'=>$filepath,'description'=>$config['desc'],'version'=>$config['version'],'author'=>$config['author'],'update_time'=>strtotime($config['update_time']),'module'=>$config['module'],'isopen'=>0,'config'=>$plg_old['config'],'addtime'=>time()];
						}else{
							$w = ['name'=>$config['name'],'filepath'=>$filepath,'description'=>$config['desc'],'version'=>$config['version'],'author'=>$config['author'],'update_time'=>strtotime($config['update_time']),'module'=>$config['module'],'isopen'=>0,'config'=>'','addtime'=>time()];
						}
						//复制文件到对应文件夹
						//移动前台插件控制器
						$sourcefile = $dir.'/'.$filepath.'/controller/home';
						$target = APP_PATH.'Home/plugins';
						if(is_dir($sourcefile) && is_dir($target)){
							if (false != ($handle = opendir ( $sourcefile ))) {
							
								while ( false !== ($file = readdir ( $handle )) ) {
									//去掉"“.”、“..”以及带“.xxx”后缀的文件
									if ($file != "." && $file != "..") {
										$fs = $sourcefile.'/'.$file;
										$ft = $target.'/'.$file;
										$r = $this->file2dir($fs,$ft);
										if(!$r){
											JsonReturn(array('code'=>1,'msg'=>'插件安装失败！sourcefile:'.$fs.' targetfile:'.$ft));
										}
									}
								}
								//关闭句柄
								closedir ( $handle );
							}
							
						}
						
						//移动后台插件控制器
						$sourcefile = $dir.'/'.$filepath.'/controller/admin';
						$target = APP_PATH.'A/plugins';
						if(is_dir($sourcefile) && is_dir($target)){
							if (false != ($handle = opendir ( $sourcefile ))) {
								
								while ( false !== ($file = readdir ( $handle )) ) {
									//去掉"“.”、“..”以及带“.xxx”后缀的文件
									if ($file != "." && $file != "..") {
										$fs = $sourcefile.'/'.$file;
										$ft = $target.'/'.$file;
										$r = $this->file2dir($fs,$ft);
										if(!$r){
											JsonReturn(array('code'=>1,'msg'=>'插件安装失败！sourcefile:'.$fs.' targetfile:'.$ft));
										}
									}
								}
								//关闭句柄
								closedir ( $handle );
							}
						
						}
						
						
						//移动扩展类文件
						$src = $dir.'/'.$filepath.'/class';
						$dst = APP_PATH.'FrPHP/Extend';
						if(is_dir($src)){
							$this->recurse_copy($src,$dst);
						}
						
						
						$res = M('plugins')->update(['filepath'=>$filepath],$w);
						
						setCache('hook',null);
						
						
						JsonReturn(array('code'=>0,'msg'=>'安装成功！'));
				    	break;
				    default:
				        # code...
				        break;
				}
			}
			$config = require_once(APP_PATH.'A/exts/'.$filepath.'/config.php');
			$r = file_get_contents('http://api.jizhicms.cn/plugins.php?name='.$filepath.'&v='.$config['version']);
			$rr = json_decode($r,1);
			if($rr['code']==0){
				$this->plugin = $config;
				$this->data = $rr['data'];
				//获取远程文件大小
				$downurl = $rr['data']['url'];
				ob_start(); 
				$ch=curl_init($downurl); 
				curl_setopt($ch,CURLOPT_HEADER,1); 
				curl_setopt($ch,CURLOPT_NOBODY,1); 
				$okay=curl_exec($ch); 
				curl_close($ch); 
				$head=ob_get_contents(); 
				ob_end_clean(); 
				$regex='/Content-Length:\s([0-9].+?)\s/'; 
				$count=preg_match($regex,$head,$matches); 
				$filesize = isset($matches[1])&&is_numeric($matches[1])?$matches[1]:0; 
				$this->filesize = $filesize;
				$this->filepath = $filepath;
				$this->display('plugins-update');exit;
			}else{
				//JsonReturn(array('code'=>1,'msg'=>'该插件暂无更新！'));
				exit('该插件暂无更新！');
			}

			
		}
		JsonReturn(array('code'=>1,'msg'=>'参数错误,必须携带插件ID！'));
	}

	

	
}