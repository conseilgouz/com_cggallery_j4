<?php
/**
 * @component     CG Gallery for Joomla 4.x/5.x
 * Version			: 3.0.4
 *
 * @author     ConseilgGouz
 * @copyright (C) 2024 www.conseilgouz.com. All Rights Reserved.
 * @license    GNU/GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ConseilGouz\Component\CGGallery\Administrator\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\FolderlistField;

// Prevent direct access
defined('_JEXEC') || die;

class CgfolderlistField extends FolderlistField
{
    /**
     * Element name
     *
     * @var   string
     */
    protected $_name = 'cgfolderlist';

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $base_dir = ComponentHelper::getParams('com_cggallery')->get('base_dir','images');
        $return = parent::setup($element, $value, $group);
        // Get the path in which to search for file options.
        $this->directory = (string) $base_dir;

        return $return;

    }
}
