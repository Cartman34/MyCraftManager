<?php

abstract class AdminController extends HTTPController {
	
	protected $breadcrumb	= array();

	public function addBreadcrumb($label, $link=null) {
		$this->breadcrumb[]	= (object)array('label' => $label, 'link' => $link);
	}

	public function addRouteToBreadcrumb($route, $link=true) {
		$this->addBreadcrumb(t($route), $link ? u($route, is_array($link) ? $link : array()) : null);
	}

	public function addThisToBreadcrumb($link=false) {
		$this->addRouteToBreadcrumb($this->getRouteName(), $link);
	}
	
	public function preRun(HTTPRequest $request) {
		parent::preRun($request);
		HTMLRendering::setDefaultTheme('admin');
		
		$this->addBreadcrumb(t('home'), u(DEFAULTMEMBERROUTE));
		if( DEFAULTMEMBERROUTE !== $this->getRouteName() ) {
			$this->addRouteToBreadcrumb(DEFAULTMEMBERROUTE);
		}
		
		/* @var $USER User */
// 		if( CHECK_MODULE_ACCESS ) {
// 			global $USER;
// 			if( !$USER || !$USER->canAccess($request->getRouteName()) ) {
// 				throw new ForbiddenException('forbiddenAccessToRoute');
// 			}
// 		}
	}
	
	public function render($response, $layout, $values=array()) {
		if( isset($GLOBALS['USER']) ) {
			$values['USER']	= $GLOBALS['USER'];
		}
		$values['Breadcrumb']	= $this->breadcrumb;
		return parent::render($response, $layout, $values);
	}

}
