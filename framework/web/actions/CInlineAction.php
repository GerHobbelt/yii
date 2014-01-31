<?php
/**
 * CInlineAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CInlineAction represents an action that is defined as a controller method.
 *
 * The method name is like 'actionXYZ' where 'XYZ' stands for the action name.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.actions
 * @since 1.0
 */
class CInlineAction extends CAction
{
	/**
	 * Runs the action.
	 * The action method defined in the controller is invoked.
	 * This method is required by {@link CAction}.
	 */
	public function run()
	{
		$method='action'.$this->getId();
		if(YII_DEBUG_ROUTING) Yii::trace("run { method = " . var_dump_ex_txt($method) . " }", "framework.web.actions.CInlineAction");
		$this->getController()->$method();
	}

	/**
	 * Runs the action with the supplied request parameters.
	 * This method is internally called by {@link CController::runAction()}.
	 * @param array $params the request parameters (name=>value)
	 * @return boolean whether the request parameters are valid
	 * @since 1.1.7
	 */
	public function runWithParams($params)
	{
		$methodName='action'.$this->getId();
		$controller=$this->getController();
		$method=new ReflectionMethod($controller, $methodName);
		if(YII_DEBUG_ROUTING) Yii::trace("runWithParams(params = " . var_dump_ex_txt($params) . ") { methodName = " . var_dump_ex_txt($methodName) . ", controller = " . var_dump_ex_txt($controller) . ", method = " . var_dump_ex_txt($method) . " }", "framework.web.actions.CInlineAction");
		if($method->getNumberOfParameters()>0)
		{
			return $this->runWithParamsInternal($controller, $method, $params);
		}
		else
		{
			return $controller->$methodName();
		}
	}

}
