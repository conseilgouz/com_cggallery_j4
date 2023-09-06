<?php
/**
 * @component     CG Gallery
 * Version			: 2.4.4
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2023 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
**/
namespace ConseilGouz\Component\CGGallery\Site\Helper;
 
\defined('_JEXEC') or die;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Content\Site\Model\ArticleModel;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentHelperRoute;
use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Router\Route;
use  Joomla\CMS\Filter\OutputFilter as FilterOutput;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\String\StringHelper;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Path;
class CGHelper {
    public static function getParams($id,$model)
    {
		$table = $model->getTable();
		$table->load((int)$id);
		$lesparams = json_decode($table->page_params,true);
		$params = new Registry(json_encode($lesparams));
        $params->set('slides',$table->slides); 
		return $params;
    }
	public static function getFolder($dir)
	{
		$input = Factory::getApplication()->input;
		$catid = 0;
		$articleid = 0;
		$articlealias = '';
		$catalias = '';
		if ($input->getString('view') == 'category') {
			$catid = $input->getInt('id');
			$catalias = self::getCatAlias($catid)->alias;
		}
		if ($input->getString('view') == 'article') { // on est sur l'affichage d'un article
			$catid = $input->getInt('catid');
			$articleid = $input->getInt('id');
			$res = self::getArticleInfos($articleid);
			$articlealias = $res->alias;
			$catid = $res->catid;
			$catalias = self::getCatAlias($catid)->alias;
		}
		$root = $dir;
		$pattern = array('/\$catid/','/\$catalias/', '/\$articleid/', '/\$articlealias/');
		$replace = array($catid, $catalias, $articleid,$articlealias);
		$root = preg_replace($pattern, $replace, $root);
		if (strpos($root,'$') !== false) { // répertoire incorrect: il reste des zones 
			return false;
		}
		$root = Path::clean($root,'/');
		if(!is_dir($root) ) { // le répertoire n'existe pas : on crée
			Folder::create($root,755);
		}
		return $root; 
	}
	static function getArticleInfos($id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('alias,catid')
			->from('#__content')
			->where(' id = ' .$id);
		$db->setQuery($query);
		return $db->loadObject();
	}
	static function getCatAlias($id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('alias')
			->from('#__categories')
			->where(' id = ' .$id);
		$db->setQuery($query);
		return $db->loadObject();
	}
	static function getArticle(&$item, $params) {
		$model     = new ArticleModel(array('ignore_request' => true));
		$app       = Factory::getApplication();
		$appParams = $app->getParams();
		$params= $appParams;
		$model->setState('params', $appParams);
		
		$model->setState('list.start', 0);
		$model->setState('list.limit', 1);
		// $model->setState('filter.published', 1);
		$model->setState('filter.featured', 'show');
		$model->setState('filter.category_id', array());
		
		// Access filter
		$access =ComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
		$model->setState('filter.access', $access);
		
		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());
		// Ordering
		$model->setState('list.ordering', 'a.hits');
		$model->setState('list.direction', 'DESC');
		
		$onearticle = $model->getItem($item->file_id);
		
		$item->article = $onearticle;
		$item->article->text = $onearticle->introtext;
		$item->article->text = self::truncate($item->article->text, $params->get('ug_text_lgth', '100'), true, false);
		// set the item link to the article depending on the user rights
		if ($access || in_array($item->article->access, $authorised)) {
			// We know that user has the privilege to view the article
			$item->slug = $item->article->id . ':' . $item->article->alias;
			$item->catslug = $item->article->catid ? $item->article->catid . ':' . $item->article->category_alias : $item->article->catid;
			$link = Route::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
		} else {
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');
			if (isset($menuitems[0])) {
				$Itemid = $menuitems[0]->id;
			} elseif (Factory::getApplication()->input::getInt('Itemid') > 0) {
				$Itemid = Factory::getApplication()->input::getInt('Itemid');
			}
			$link = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
		}
		return $link;
	}
	static function getArticleK2(&$item, $params) {
		require_once(JPATH_SITE.'/components/com_k2/helpers/route.php');
//		require_once(JPATH_SITE.'/components/com_k2/helpers/utilities.ph//p');		
	// Access filter
        $app = Factory::getApplication();
        $db = Factory::getDbo();
		$query = "SELECT i.* from #__k2_items AS i WHERE i.id = {$item->file_id}";
        $db->setQuery($query);
        $item2 = $db->loadObject();
        $link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item2->id.':'.urlencode($item2->alias), $item2->catid.':'.urlencode($item2->categoryalias))));
		return $link;
	}
	/**
	 * Truncates text blocks over the specified character limit and closes
	 */
    public static function truncate($html, $maxLength = 0, $noSplit = true, $allowHtml = true)
    {
	        $baseLength = strlen($html);
	        $ptString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit, $allowHtml);
	        for ($maxLength; $maxLength < $baseLength;)
	        {
	            $htmlString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit, $allowHtml);
	            $htmlStringToPtString = HTMLHelper::_('string.truncate', $htmlString, $maxLength, $noSplit, $allowHtml);
	            if ($ptString == $htmlStringToPtString)
	            {
	                return $htmlString;
	            }
	            $diffLength = strlen($ptString) - strlen($htmlStringToPtString);
	            $maxLength += $diffLength;
	            if ($baseLength <= $maxLength || $diffLength <= 0)
	            {
	                return $htmlString;
	            }
	        }
	        return $html;
    }

	/* 
		fichier desc.txt
		<nom image>|<description>|<url>
		si nom image = * => description/url par défaut
	*/
	static public function getDesc($dir) {
		$filename = 'desc';
		$contents = self::getLabelsFileContents($dir,$filename);
		if ($contents === false) {
			return array();
		}
		if (!strcmp("\xEF\xBB\xBF", substr($contents,0,3))) {  // file starts with UTF-8 BOM
			$contents = substr($contents, 3);  // remove UTF-8 BOM
		}
		// $contents = str_replace("\r", "\n", $contents);  // normalize line endings
		$contents = strtr($contents, 'áàâäãåçéèêëíìîïñóòôöõúùûüýÿ', 'aaaaaaceeeeiiiinooooouuuuyy');
		// split into lines
		$matches = array();
		preg_match_all('/^([^|\n]+)(?:[|]([^|\n]*)(?:[|]([^\n]*))?)?$/mu', $contents, $matches, PREG_SET_ORDER);

		// parse individual entries
		$labels = array();
		foreach ($matches as $match) {
			$imagefile = $match[1];
			$description = count($match) > 2 ? $match[2] : false;
			$link = count($match) > 3 ? $match[3] : false;
			$arr = array();
			$arr['imagefile'] = $imagefile;
			$arr['description'] = $description;
			$arr['link'] = $link;
			$labels[$imagefile] = $arr; 
		}
		return $labels;		
	}
private static function getLabelsFileContents($imagedirectory, $labelsfilename) {
		$file = self::getLabelsFilePath($imagedirectory, $labelsfilename);
		return $file ? file_get_contents($file) : false;
	}

private static function getLabelsFilePath($imagedirectory, $labelsfilename) {

		if (is_file($imagedirectory)) {  // a file, not a directory
			return false;
		}

		// default to language-neutral labels file
		$file = $imagedirectory.DIRECTORY_SEPARATOR.$labelsfilename.'.txt';  // filesystem path to labels file
		if (is_file($file)) {
			return $file;
		}
		return false;
	}	
	public static function thumbnailFromDir($dir,$compression) {
		$files = Folder::files($dir,null,null ,null ,null , array('desc.txt','index.html','.htaccess'));
		$_dir = $dir;
		$nb = 0;
		if (count($files) > 0) {
			foreach ($files as $file) {
				$imgthumb = $file;
				$pos = strrpos($imgthumb,'/');
				$len = strlen($imgthumb);
				$imgthumb = $_dir.'/th/'.substr($imgthumb,$pos,$len);
				if (!File::exists($imgthumb)) { // fichier existe déjà  : on sort
					if (self::createThumbNail(URI::root().$_dir.'/'.$file,$imgthumb,$compression)) {
					   $nb = $nb+1;
					} else {
					    Factory::getApplication()->enqueueMessage(Text::_($this->errorMsg), 'notice');
					}
				}

			}
			if ($nb > 0) {
				Factory::getApplication()->enqueueMessage($nb.Text::_('SAG_THUMBCREATION') );
			}
	
		}
	}
	public static function createThumbNail($fileIn,$fileOut,$compression) {
		list($w, $h, $type) = getimagesize($fileIn);
		$width = $w;
		$height = $h;
		$scale = (($width / $w) > ($height / $h)) ? ($width / $w) : ($height / $h); // greater rate
		$newW = $width/$scale;    // check the size of in file
		$newH = $height/$scale;
		$errorMsg ='';
		// which side is larger (rounding error)
		if (($w - $newW) > ($h - $newH)) {
			$src = array(floor(($w - $newW)/2), 0, floor($newW), $h);
		} else {
			$src = array(0, floor(($h - $newH)/2), $w, floor($newH));
		}
		$dst = array(0,0, floor($width), floor($height));
		switch($type) {
			case IMAGETYPE_JPEG:
				if (!function_exists('imagecreatefromjpeg')) {
					return 'ErrorNoJPGFunction';
				}
				try {
					$image1 = imagecreatefromjpeg($fileIn);
				} catch(\Exception $exception) {
					return 'ErrorJPGFunction';
				}
				break;
			case IMAGETYPE_PNG :
				if (!function_exists('ImageCreateFromPNG')) {
					return 'ErrorNoPNGFunction';
				}
				try {
					$image1 = ImageCreateFromPNG($fileIn);
				} catch(\Exception $exception) {
					return 'ErrorPNGFunction';
				}
				break;
			case IMAGETYPE_GIF :
				if (!function_exists('ImageCreateFromGIF')) {
					return 'ErrorNoGIFFunction';
				}
				try {
					$image1 = ImageCreateFromGIF($fileIn);
				} catch(\Exception $exception) {
					return 'ErrorGIFFunction';
				}
				break;
			case IMAGETYPE_WBMP:
				if (!function_exists('ImageCreateFromWBMP')) {
					return 'ErrorNoWBMPFunction';
				}
				try {
					$image1 = ImageCreateFromWBMP($fileIn);
				} catch(\Exception $exception) {
					return 'ErrorWBMPFunction';
				}
				break;
			Default:
				return 'ErrorNotSupportedImage';
				break;
		}
		if ($image1) {
	
			$image2 = @ImageCreateTruecolor($dst[2], $dst[3]);
			if (!$image2) {
				return 'ErrorNoImageCreateTruecolor';
			}
	
			ImageCopyResampled($image2, $image1, $dst[0],$dst[1], $src[0],$src[1], $dst[2],$dst[3], $src[2],$src[3]);
	
			// Create the file
			$typeOut = ($type == IMAGETYPE_WBMP) ? IMAGETYPE_PNG : $type;
			header("Content-type: ". image_type_to_mime_type($typeOut));
	
			switch($typeOut) {
				case IMAGETYPE_JPEG:
					if (!function_exists('ImageJPEG')) {
						return 'ErrorNoJPGFunction';
					}
					ob_start();
	
					if (!imagejpeg($image2,NULL,$compression)) {
						ob_end_clean();
						return 'ErrorWriteFile';
					}
					$imgJPEGToWrite = ob_get_contents();
					ob_end_clean();
					if(!File::write($fileOut, $imgJPEGToWrite)) {
						return 'ErrorWriteFile';
					}
					break;
	
				case IMAGETYPE_PNG :
					if (!function_exists('ImagePNG')) {
						return 'ErrorNoPNGFunction';
					}
	
					if (!@ImagePNG($image2, NULL,$compression)) {
						return 'ErrorWriteFile';
					}
					$imgGIFToWrite = ob_get_contents();
					ob_end_clean();
					if(!File::write('../'.$fileOut, $imgGIFToWrite)) {
						return 'ErrorWriteFile';
					}
	
					break;
	
				case IMAGETYPE_GIF :
					if (!function_exists('ImageGIF')) {
						return 'ErrorNoGIFFunction';
					}
	
					if ($jfile_thumbs == 1) {
						ob_start();
						if (!@ImageGIF($image2, NULL,$compression)) {
							ob_end_clean();
							return 'ErrorWriteFile';
						}
						$imgGIFToWrite = ob_get_contents();
						ob_end_clean();
							
						if(!File::write('../'.$fileOut, $imgGIFToWrite)) {
							return 'ErrorWriteFile';
						}
					} else {
						if (!@ImageGIF($image2, $fileOut)) {
							return 'ErrorWriteFile';
						}
					}
					break;
	
				Default:
					return 'ErrorNotSupportedImage';
					break;
			}
			// free memory
			ImageDestroy($image1);
			ImageDestroy($image2);
	
		}
		return true;
	}	
	
}
