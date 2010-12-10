<?php
/**
 * @author Qiong Wu <papa0924@gmail.com> 2010-11-22
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2110 phpwind.com
 * @license 
 */

/**
 * 操作转发类，将操作句柄转发给下一个操作或者转发给一个视图处理
 * the last known user to change this file in the repository  <$LastChangedBy$>
 * @author Qiong Wu <papa0924@gmail.com>
 * @version $Id$ 
 * @package 
 */
class WindForward {
	
	/* 模板视图信息 */
	private $templateName; //模板名称
	private $templatePath; //模板路径
	private $templateConfig; //模板配置支持
	

	/* 布局信息 */
	private $layout = null;
	
	/* 操作处理请求 */
	private $action;
	private $actionPath;
	
	/* 页面重定向请求信息 */
	private $redirect = '';
	private $isRedirect = false;
	
	/* 模板数据信息 */
	private $data;
	
	/**
	 * 设置视图的逻辑名称
	 * 
	 * @param string $name
	 */
	public function setTemplateName($templateName) {
		$this->templateName = $templateName;
	}
	
	/**
	 * 设置视图的路径信息
	 * 
	 * @param string $path
	 */
	public function setTemplatePath($templatePath) {
		$this->templatePath = $templatePath;
	}
	
	/**
	 * 设置模板配置
	 * 
	 * @param string $templateConfigName
	 */
	public function setTemplateConfig($templateConfigName) {
		$this->templateConfig = $templateConfigName;
	}
	
	/**
	 * @param WindLayout $layout
	 */
	public function setLayout($layout) {
		$this->layout = $layout;
	}
	
	/**
	 * 设置视图的重定向信息
	 * 
	 * @param string $redirect
	 */
	public function setRedirect($redirect) {
		$this->redirect = $redirect;
		$this->isRedirect = true;
	}
	
	/**
	 * @param $action the $action to set
	 * @author Qiong Wu
	 */
	public function setAction($action, $path = '', $isRedirect = false) {
		$this->action = $action;
		$this->isRedirect = $isRedirect;
		$this->actionPath = $path;
	}
	
	/**
	 * 获得重定向链接
	 * @return string
	 */
	public function getRedirect() {
		return $this->redirect;
	}
	
	/**
	 * 返回视图的逻辑名称
	 * 
	 * @return string
	 */
	public function getTemplateName() {
		return $this->templateName;
	}
	
	/**
	 * 返回WindView对象
	 * @return WindView
	 */
	public function getView() {
		//TODO
		L::import('WIND:component.viewer.WindView');
		$this->view = new WindView($this, $this->templateConfig);
		return $this->view;
	}
	
	/**
	 * 返回视图的路径信息
	 * 
	 * @return string
	 */
	public function getTemplatePath() {
		return $this->templatePath;
	}
	
	/**
	 * @return the $action
	 */
	public function getAction() {
		return $this->action;
	}
	
	/**
	 * @return the $actionPath
	 */
	public function getActionPath() {
		return $this->actionPath;
	}
	
	/**
	 * @return WindLayout
	 */
	public function getLayout() {
		return $this->layout;
	}
	
	/**
	 * @return the $isRedirect
	 */
	public function isRedirect() {
		return $this->isRedirect;
	}
}