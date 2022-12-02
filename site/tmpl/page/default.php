<?php
/**
 * @component     CG Gallery
 * Version			: 2.3.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2022 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
**/
 
// no direct access

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use ConseilGouz\Component\CGGallery\Site\Helper\CGHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;

$document = Factory::getDocument();
// HTMLHelper::_('bootstrap.framework');
HTMLHelper::_('jquery.framework'); 
$comfield	= 'media/com_cggallery/';
$app = Factory::getApplication();
$com_id = $app->input->getInt('Itemid');

$uri = Uri::getInstance();

$this->cgg_params = CGHelper::getParams($this->page,$this->getModel());
$ug_type	= $this->cgg_params->get('ug_type', '');
$ug_texte	= $this->cgg_params->get('ug_texte', '');
$ug_tiles_type = $this->cgg_params->get('ug_tiles_type', '' );
$ug_big_dir = $this->cgg_params->get('ug_big_dir','');
$ug_grid_num_rows = $this->cgg_params->get('ug_grid_num_rows');
$ug_space_between_rows = $this->cgg_params->get('ug_space_between_rows');
$ug_space_between_cols = $this->cgg_params->get('ug_space_between_cols');
$ug_min_columns = $this->cgg_params->get('ug_min_columns');
$ug_tile_height = $this->cgg_params->get('ug_tile_height');
$ug_tile_width = $this->cgg_params->get('ug_tile_width');
$ug_carousel_autoplay_timeout = $this->cgg_params->get('ug_carousel_autoplay_timeout');
$ug_carousel_scroll_duration = $this->cgg_params->get('ug_carousel_scroll_duration');
$ug_link = $this->cgg_params->get('ug_link');
$ug_lightbox = $this->cgg_params->get('ug_lightbox');
$ug_zoom = $this->cgg_params->get('ug_zoom','true');
$ug_grid_thumbs_pos = $this->cgg_params->get('ug_grid_thumbs_pos');
$ug_grid_show_icons = $this->cgg_params->get('ug_grid_show_icons');

$ug_articles = $this->cgg_params->get('ug_articles','articles');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

$wa->registerAndUseStyle('unitegallery', $comfield.'unitegallery/css/unite-gallery.css');
$wa->registerAndUseScript('unitegallery', $comfield.'unitegallery/js/unitegallery.min.js');

if ($ug_type == "tiles") {
	if ($ug_tiles_type == "tilesgrid") {
		$wa->registerAndUseScript('tilesgrid', $comfield.'unitegallery/themes/tilesgrid/ug-theme-tilesgrid.js');
	} else {
		$wa->registerAndUseScript('tiles', $comfield.'unitegallery/themes/tiles/ug-theme-tiles.js');
	}
}
if ($ug_type == "grid") {
		$wa->registerAndUseScript('grid', $comfield.'unitegallery/themes/grid/ug-theme-grid.js');
}
if ($ug_type == "carousel") {
		$wa->registerAndUseScript('carousel', $comfield.'unitegallery/themes/carousel/ug-theme-carousel.js');
}
if ($ug_type == "slider") {
		$wa->registerAndUseScript('slider', $comfield.'unitegallery/themes/slider/ug-theme-slider.js');
}

if (strlen(trim($this->cgg_params->get('intro'))) > 0) {
	// apply content plugins on weblinks
	$item_cls = new stdClass;
	$item_cls->text = $this->cgg_params->get('intro');
	$item_cls->params = $this->cgg_params;
    $item_cls->id= $com_id;
	Factory::getApplication()->triggerEvent('onContentPrepare', array ('com_cggallery.content', &$item_cls, &$item_cls->params, 0)); // Joomla 4.0
	$intro = 	$item_cls->text;	
	echo $intro; 
}
	
echo '<div id="cg_gallery_'.$com_id.'" data="'.$com_id.'" class="cg_gallery">';
if ($this->cgg_params->get('ug_dir_or_image') == "dir") { // images d'un répertoire
	$ug_full_dir = $this->cgg_params->get('ug_full_dir',''); // répertoire complet ou non
	$files = array();
	$ug_big_dir = CGHelper::getFolder( $ug_big_dir); // gestion répertoire dynamique
	if ($ug_big_dir === false) {
		echo '</div>';  // on ferme la div ouverte
		return false;
	}
	if (strpos($this->cgg_params->get('ug_big_dir',''),'$') !== false) { // on a un répertoire paramétrable
		CGHelper::thumbnailFromDir($ug_big_dir,$this->cgg_params->get('ug_compression'));
	}
	$files = Folder::files($ug_big_dir,null,null ,null , array('desc.txt','index.html','.htaccess'));
	$desc = CGHelper::getDesc($ug_big_dir); // récupération fichier description s'il existe
	if (count($files) == 0) { ?>
		<img alt=""
		src ="<?php echo ''.URI::base(true).'/'.$comfield;?>unitegallery/images/pasdimage.png" 
		data-image="<?php echo ''.URI::base(true).'/'.$comfield;?>unitegallery/images/pasdimage.png" 
						data-description=""
						style="display:none">
	<?php 
	} else {
	$ug_thumb_dir = $ug_big_dir.'/th/'; // répertoire des miniatures
	if ($ug_full_dir == "true") { // répertoire complet
		foreach ($files as $file) { 
			$bigfile = $file;
			$description = $bigfile;
			$link = false;
			$item = new stdClass();
			$index = isset($desc[$bigfile]) ? $bigfile : '*';
			if ($index == '*') {if (!isset($desc[$index])) {$index = false;}}
			if ($index) {
				$description = $desc[$index]['description'];
				$link = trim($desc[$index]['link']);
				$target = '';
				if (is_numeric($link)) { // lien sur un article
					$item->slidearticleid = $link;
					$item->file_id = $link;
					$link = CGHelper::getArticle($item, $this->cgg_params);
					$description = $item->article->text;
				} else {
				$target = ' target="_blank" rel="noopener noreferrer" '; // lien sur adresse web: nouvelle fenêtre
				}
				
			}
			if ($link) {
				echo '<a href="'.$link.'"'.$target.'>';
			}
			?>
			<img alt="<?php echo $bigfile;?>" 
			src="<?php if (!File::exists($ug_thumb_dir.$file)) {// create thumbnail file if it does not exist
						CGHelper::createThumbNail($ug_big_dir.'/'.$bigfile,$ug_thumb_dir.$file,$this->cgg_params->get('ug_compression'));
					}
					echo $uri->root().$ug_thumb_dir.$file;?>"
			<?php if (File::exists($ug_big_dir.'/'.$bigfile)) {
			?>
				data-image="<?php echo $uri->root().$ug_big_dir; ?>/<?php echo $bigfile;?>"
			<?php } else {?>
				data-image="<?php echo $uri->root().$comfield;?>unitegallery/images/pasdimage.png" <?php } ?>
				data-description="<?php echo $description;?>"
				style="display:none">
		<?php
			if ($link) { echo '</a>'; }
			} 
	}  else { // on prend juste les premières images du répertoire
			$ug_file_nb = $this->cgg_params->get('ug_file_nb');
			if ($ug_file_nb > count($files) ) {$ug_file_nb = count($files); } // dépassement capacité
			for ($i = 0; $i < $ug_file_nb; $i++) { 
				$bigfile = $files[$i];
				$description = $bigfile;
				$item = new stdClass();
				$link = false;
				$target = '';
				$index = isset($desc[$bigfile]) ? $bigfile : '*';
				if ($index == '*') {if (!isset($desc[$index])) {$index = false;}}
				if ($index) {
					$description = $desc[$index]['description'];
					$link = trim($desc[$index]['link']);
					if (is_numeric($link)) { // lien sur un article
						$item->slidearticleid = $link;
						$item->file_id = $link;
						$link = CGHelper::getArticle($item, $this->cgg_params);
						$description = $item->article->text;
					}
					else {
					$target = ' target="_blank" rel="noopener noreferrer" '; // lien sur adresse web: nouvelle fenêtre
					}
				}
				if ($link) {
					echo '<a href="'.$link.'"'.$target.'>'; // lien externe : nouvelle fenêtre
				}
				?>
				<img alt="<?php echo $bigfile;?>" 
					src="<?php 
					if (!File::exists($ug_thumb_dir.'/'.$files[$i])) { // create thumbnail file if it does not exist
						CGHelper::createThumbNail($ug_big_dir.'/'.$files[$i],$ug_thumb_dir.'/'.$files[$i],$this->cgg_params->get('ug_compression'));
					}
					echo $uri->root().$ug_thumb_dir.'/'.$files[$i];?>"
				<?php if (File::exists($ug_big_dir.'/'.$files[$i])) { ?>
					data-image="<?php echo $uri->root().$ug_big_dir; ?>/<?php echo $bigfile;?>"
				<?php } else {?>
					data-image="<?php echo $uri->root().$comfield;?>unitegallery/images/pasdimage.png" 
				<?php } ?>
				data-description="<?php echo $description;?>"
				style="display:none">
			<?php 
				if ($link) { echo '</a>'; }
			}
	    }
	}
}  else { // images sélectionnées individuellement
	$slideslist = json_decode($this->cgg_params->slides);
	foreach ($slideslist as $item) {
		$imgcaption =  $item->file_desc;
		$image40 = explode('#',$item->file_name);
		$imgname = $image40[0]; // Joomla 4.0 : nom du fichier en 2 parties
		$imgthumb = $imgname;
		$pos = strrpos($imgthumb,'/');
		$len = strlen($imgthumb);
		$imgthumb = substr($imgthumb,0,$pos+1).'th/'.substr($imgthumb,$pos+1,$len);
		$imgtitle = $item->file_name;
		$slidearticleid = $item->file_id;
		$link = null;
		$imgdesc = $imgcaption;
		if (isset($slidearticleid) && $slidearticleid) {
			if ($ug_articles == 'articles') {
				$link = CGHelper::getArticle($item, $this->cgg_params);	
			} else {
				$link = CGHelper::getArticleK2($item, $this->cgg_params);	
			}
			if ($imgdesc == '') $imgdesc = $item->article->text; // imgdesc empty => take article
		}
		if (isset($link)) {
			echo '<a href="'.$link.'">';
		}
		?>
				<img alt="<?php echo $imgcaption;?>" 
					src="<?php 
					if (!File::exists($imgthumb)) {// create thumbnail file if it does not exist
						CGHelper::createThumbNail($imgname,$imgthumb,$this->cgg_params->get('ug_compression'));
					}
					echo $imgthumb;
					?>"
					<?php if ($imgname)
					{
					    ?> data-image="<?php echo $uri->root().$imgname; ?>"
					<?php }
					else { ?> 
					data-image="<?php echo $uri->root().$comfield;?>unitegallery/images/pasdimage.png" 
					<?php } ?>
				data-description="<?php echo $imgdesc;?>"
				style="display:none">
	<?php
				if (isset($link)) { echo '</a>'; }
		} 
}
?>	
	</div>
	<?php 
$document->addScriptOptions('cg_gallery_'.$com_id, 
	array('ug_type' => $ug_type,'ug_texte' => $ug_texte,
		  'ug_tiles_type' => $ug_tiles_type,
		  'ug_grid_num_rows' => $ug_grid_num_rows,
	      'ug_space_between_rows' => $ug_space_between_rows,'ug_space_between_cols' => $ug_space_between_cols,
		  'ug_min_columns' => $ug_min_columns,
		  'ug_tile_height' => $ug_tile_height,
		  'ug_tile_width' => $ug_tile_width,
		  'ug_carousel_autoplay_timeout' => $ug_carousel_autoplay_timeout,
		  'ug_carousel_scroll_duration' => $ug_carousel_scroll_duration,
		  'ug_link'=> $ug_link,,'ug_zoom'=>$ug_zoom,
		  'ug_lightbox'=>$ug_lightbox,
		  'ug_grid_thumbs_pos'=>$ug_grid_thumbs_pos, 'ug_grid_show_icons'=>$ug_grid_show_icons
));

$wa->registerAndUseScript('init', $comfield.'js/init.js');

if (strlen(trim($this->cgg_params->get('bottom'))) > 0) {
	// apply content plugins on weblinks
	$item_cls = new stdClass;
	$item_cls->text = $this->cgg_params->get('bottom');
	$item_cls->params = $this->params;
    $item_cls->id= $com_id;
	Factory::getApplication()->triggerEvent('onContentPrepare', array ('com_cgisotope.content', &$item_cls, &$item_cls->params, 0)); // Joomla 4.0
	$bottom = 	$item_cls->text;	
	echo $bottom; 
	}
?>
