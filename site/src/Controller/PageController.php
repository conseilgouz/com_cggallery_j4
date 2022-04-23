<?php
/**
 * @component     CG Gallery
 * Version			: 2.1.1
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2022 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
**/
namespace ConseilGouz\Component\CGGallery\Site\Controller;

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;

// require_once JPATH_COMPONENT.'/controller.php';
class PageController extends BaseController
{
	public function getModel($name = 'Page', $prefix = 'CGGalleryModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}