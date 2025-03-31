<?php
/**
 * @component     CG Gallery
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @copyright (c) 2025 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
**/
namespace ConseilGouz\Component\CGGallery\Administrator\Field;
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
/* cette classe est utile pour le choix menu : affichage des pages */
class PagesField extends ListField
{
    public $type = 'Pages';

    public function getOptions()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('s.id AS value')
            ->select('s.title AS text')
            ->from('#__cggallery_page AS s')
            ->where('s.state = 1');
        $db->setQuery($query);

        $options = $db->loadObjectList();

        array_unshift($options, HtmlHelper::_('select.option', '', Text::_('JSELECT')));

        return array_merge(parent::getOptions(), $options);
    }

}