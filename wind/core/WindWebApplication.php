<?php
/**
 * @author Qiong Wu <papa0924@gmail.com> 2010-11-7
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2110 phpwind.com
 * @license 
 */

L::import('WIND:core.base.IWindApplication');
L::import('WIND:component.exception.WindException');
L::import('WIND:component.viewer.WindViewFactory');
/**
 * the last known user to change this file in the repository  <$LastChangedBy$>
 * @author Qiong Wu <papa0924@gmail.com>
 * @version $Id$ 
 * @package 
 */
class WindWebApplication implements IWindApplication {
	
	/**
	 * 初始化配置信息
	 * @param WSystemConfig $configObj
	 */
	public function init() {}
	
	/**
	 * @param WindHttpRequest $request
	 * @param WindHttpResponse $response
	 * @param WSystemConfig $configObj
	 */
	public function processRequest($request, $response) {
		$router = $this->createRouter();
		$router->doParser($request, $response);
		
		/* 获得操作句柄 */
		list($action, $method) = $this->getActionHandle($request, $response);
		$action->beforeAction();
		$action->$method($request, $response);
		$action->afterAction();
		
		/* 获得请求跳转信息 */
		$mav = $action->getModelAndView();
		$this->processDispatch($request, $response, $mav);
	}
	
	/**
	 * 返回action类
	 * 
	 * @param WindHttpRequest $request
	 * @param WindHttpResponse $response
	 * @return array(WindAction,string)
	 */
	protected function getActionHandle($request, $response) {
		list($className, $method) = $response->getRouter()->getActionHandle();
		if ($className === null || $method === null) {
			throw new WindException('can\'t create action handle.');
		}
		$class = new ReflectionClass($className);
		$action = call_user_func_array(array($class, 'newInstance'), array($request, $response));
		return array($action, $method);
	}
	
	/**
	 * 处理页面输出与重定向
	 * 
	 * @param WindHttpRequest $request
	 * @param WindHttpResponse $response
	 * @param WindModelAndView $mav
	 */
	protected function processDispatch($request, $response, $mav) {
		WindDispatcher::getInstance($mav)->dispatch($request, $response);
	}
	
	/**
	 * 获得一个路由实例
	 * @param WSystemConfig $configObj
	 * @return WRouter
	 */
	public function &createRouter() {
		$parserConfig = C::getRouterParsers(C::getRouter('parser'));
		$parserPath = $parserConfig[IWindConfig::ROUTER_PARSERS_PATH];
		list(, $className, , $parserPath) = L::getRealPath($parserPath, true);
		L::import($parserPath);
		if (!class_exists($className)) {
			throw new WindException('The router ' . $className . ' is not exists.');
		}
		$router = new $className($parserConfig);
		return $router;
	}
	
	public function destory() {}
}