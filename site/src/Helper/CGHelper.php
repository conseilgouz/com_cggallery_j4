<?php
/**
 * @component     CG Gallery
 * Version			: 2.4.8
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @copyright (c) 2024 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz
**/

namespace ConseilGouz\Component\CGGallery\Site\Helper;

\defined('_JEXEC') or die;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

class CGHelper
{
    public static function getParams($id, $model)
    {
        $table = $model->getTable();
        $item = $table->load((int)$id);
        return $table;
    }
    public static function getFolder($dir, $model)
    {
        $input = Factory::getApplication()->input;
        $catid = 0;
        $articleid = 0;
        $articlealias = '';
        $catalias = '';
        if ($input->getString('view') == 'category') {
            $catid = $input->getInt('id');
            $catalias = $model->getCatAlias($catid)->alias;
        }
        if ($input->getString('view') == 'article') { // on est sur l'affichage d'un article
            $catid = $input->getInt('catid');
            $articleid = $input->getInt('id');
            $res = $model->getArticleInfos($articleid);
            $articlealias = $res->alias;
            $catid = $res->catid;
            $catalias = $model->getCatAlias($catid)->alias;
        }
        $root = $dir;
        $pattern = array('/\$catid/','/\$catalias/', '/\$articleid/', '/\$articlealias/');
        $replace = array($catid, $catalias, $articleid,$articlealias);
        $root = preg_replace($pattern, $replace, $root);
        if (strpos($root, '$') !== false) { // répertoire incorrect: il reste des zones
            return false;
        }
        $root = Path::clean($root, '/');
        if(!is_dir($root)) { // le répertoire n'existe pas : on crée
            Folder::create($root, 755);
        }
        return $root;
    }

    public static function getArticleK2(&$item, $params)
    {
        return '';
    }
    /**
     * Truncates text blocks over the specified character limit and closes
     */
    public static function truncate($html, $maxLength = 0, $noSplit = true, $allowHtml = true)
    {
        $baseLength = strlen($html);
        $ptString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit, $allowHtml);
        for ($maxLength; $maxLength < $baseLength;) {
            $htmlString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit, $allowHtml);
            $htmlStringToPtString = HTMLHelper::_('string.truncate', $htmlString, $maxLength, $noSplit, $allowHtml);
            if ($ptString == $htmlStringToPtString) {
                return $htmlString;
            }
            $diffLength = strlen($ptString) - strlen($htmlStringToPtString);
            $maxLength += $diffLength;
            if ($baseLength <= $maxLength || $diffLength <= 0) {
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
    public static function getDesc($dir)
    {
        $filename = 'desc';
        $contents = self::getLabelsFileContents($dir, $filename);
        if ($contents === false) {
            return array();
        }
        if (!strcmp("\xEF\xBB\xBF", substr($contents, 0, 3))) {  // file starts with UTF-8 BOM
            $contents = substr($contents, 3);  // remove UTF-8 BOM
        }
        // $contents = str_replace("\r", "\n", $contents);  // normalize line endings
        // $contents = strtr($contents, 'áàâäãåçéèêëíìîïñóòôöõúùûüýÿ', 'aaaaaaceeeeiiiinooooouuuuyy');
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
    private static function getLabelsFileContents($imagedirectory, $labelsfilename)
    {
        $file = self::getLabelsFilePath($imagedirectory, $labelsfilename);
        return $file ? file_get_contents($file) : false;
    }

    private static function getLabelsFilePath($imagedirectory, $labelsfilename)
    {

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
    public static function thumbnailFromDir($dir, $compression)
    {
        $files = Folder::files($dir, null, null, null, null, array('desc.txt','index.html','.htaccess'));
        $_dir = $dir;
        $nb = 0;
        if (count($files) > 0) {
            foreach ($files as $file) {
                $imgthumb = $file;
                $pos = strrpos($imgthumb, '/');
                $len = strlen($imgthumb);
                $imgthumb = $_dir.'/th/'.substr($imgthumb, $pos, $len);
                if (!@is_file($imgthumb)) { // fichier existe déjà  : on sort
                    if (self::createThumbNail(URI::root().$_dir.'/'.$file, $imgthumb, $compression)) {
                        $nb = $nb + 1;
                    } else {
                        Factory::getApplication()->enqueueMessage(Text::_($this->errorMsg), 'notice');
                    }
                }

            }
            if ($nb > 0) {
                Factory::getApplication()->enqueueMessage($nb.Text::_('SAG_THUMBCREATION'));
            }

        }
    }
    public static function createThumbNail($fileIn, $fileOut, $compression)
    {
        list($w, $h, $type) = getimagesize($fileIn);
        $width = $w;
        $height = $h;
        $scale = (($width / $w) > ($height / $h)) ? ($width / $w) : ($height / $h); // greater rate
        $newW = $width / $scale;    // check the size of in file
        $newH = $height / $scale;
        $errorMsg = '';
        // which side is larger (rounding error)
        if (($w - $newW) > ($h - $newH)) {
            $src = array(floor(($w - $newW) / 2), 0, floor($newW), $h);
        } else {
            $src = array(0, floor(($h - $newH) / 2), $w, floor($newH));
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
            default:
                return 'ErrorNotSupportedImage';
                break;
        }
        if ($image1) {

            $image2 = @ImageCreateTruecolor($dst[2], $dst[3]);
            if (!$image2) {
                return 'ErrorNoImageCreateTruecolor';
            }

            ImageCopyResampled($image2, $image1, $dst[0], $dst[1], $src[0], $src[1], $dst[2], $dst[3], $src[2], $src[3]);

            // Create the file
            $typeOut = ($type == IMAGETYPE_WBMP) ? IMAGETYPE_PNG : $type;
            header("Content-type: ". image_type_to_mime_type($typeOut));

            switch($typeOut) {
                case IMAGETYPE_JPEG:
                    if (!function_exists('ImageJPEG')) {
                        return 'ErrorNoJPGFunction';
                    }
                    ob_start();

                    if (!imagejpeg($image2, null, $compression)) {
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
                    ob_start();
                    if (!@ImagePNG($image2, null, $compression)) {
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
                    ob_start();
                    if (!@ImageGIF($image2, null, $compression)) {
                        ob_end_clean();
                        return 'ErrorWriteFile';
                    }
                    $imgGIFToWrite = ob_get_contents();
                    ob_end_clean();
                    if(!File::write('../'.$fileOut, $imgGIFToWrite)) {
                        return 'ErrorWriteFile';
                    }
                    break;

                default:
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
