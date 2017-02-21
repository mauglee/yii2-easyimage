<?php

namespace mauglee\easyimage;

use mauglee\easyimage\drivers\Image;
use Yii;
use yii\base\Component;
use yii\helpers\Html;

/**
 * EasyImage class file.
 *
 * @author Artur Zhdanov <zhdanovartur@gmail.com>
 * @copyright Copyright &copy; Artur Zhdanov 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version 1.0.6
 *
 * @property Image $image
 */
class EasyImage extends Component {

	/**
	 * Resizing directions
	 */
	const RESIZE_NONE = 0x01;
	const RESIZE_WIDTH = 0x02;
	const RESIZE_HEIGHT = 0x03;
	const RESIZE_AUTO = 0x04;
	const RESIZE_INVERSE = 0x05;
	const RESIZE_PRECISE = 0x06;

	/**
	 * Flipping directions
	 */
	const FLIP_HORIZONTAL = 0x11;
	const FLIP_VERTICAL = 0x12;

	/**
	 * Root path for saving files
	 *
	 * @var string
	 */
	public $basePath = '@webroot';
	/**
	 * Root URL
	 *
	 * @var string
	 */
	public $baseUrl = '@web';

	/**
	 * @var Image
	 */
	private $_image;

	/**
	 * @var string driver type: GD, Imagick
	 */
	public $driver = 'GD';

	/**
	 * @var string relative path where the cache files are kept
	 */
	public $cachePath = '/assets/easyimage/';

	/**
	 * @var int cache lifetime in seconds
	 */
	public $cacheTime = 60*60*24*180;

	/**
	 * @var int value of quality: 0-100 (only for JPEG)
	 */
	public $quality = 100;

	/**
	 * @var bool use retina-resolutions
	 * This setting increases the load on the server.
	 */
	public $retinaSupport = false;

	/**
	 * @var int Permissions for main cache directory and subdirectories.
	 */
	public $newDirMode = 0775;

	/**
	 * @var int Permissions for cached files.
	 */
	public $newFileMode = 0660;

	/**
	 * Device pixels per CSS pixel
	 * Use integer numbers
	 * If this is changed after some images are already create, you must <code>touch</code> source files to invalidate the cache
	 *
	 * @var array
	 * @see http://imulus.github.io/retinajs/
	 */
	public $pixel_ratio = [ 2 ];

	/**
	 * Convert object to binary data of current image.
	 * Must be rendered with the appropriate Content-Type header or it will not be displayed correctly.
	 *
	 * @return string as binary
	 */
	public function __toString() {

		try {
			return $this->getImage()->render();
		} catch ( \Exception $e ) {
			return '';
		}
	}

	/**
	 * @inheritdoc
	 */
	public function init() {

		// Publish "retina.js" library (http://retinajs.com/)
		if ( $this->retinaSupport ) {
			EasyImageAsset::register( Yii::$app->getView() );
		}
	}

	/**
	 * This method returns the current Image instance.
	 *
	 * @return Image
	 * @throws \Exception
	 */
	public function getImage() {

		if ( $this->_image instanceof Image ) {
			return $this->_image;
		} else {
			throw new \Exception( 'Don\'t have image' );
		}
	}

	/**
	 * @param Image|string $image
	 *
	 * @return $this
	 */
	public function setImage( $image ) {

		if ( $image instanceof Image ) {
			$this->_image = $image;
		} elseif ( is_string( $image ) ) {
			if ( $image = $this->detectPath( $image ) ) {
				$this->_image = Image::factory( $image, $this->driver );
			}
		}

		return $this;
	}

	/**
	 * This method detects which (absolute or relative) path is used.
	 *
	 * @param string $file path
	 *
	 * @return string path
	 */
	public function detectPath( $file ) {

		if ( ! is_file( $file ) ) {
			$file = rtrim( Yii::getAlias( $this->basePath ), '\\/' ) . '/' . $file;

			return is_file( $file ) ? $file : false;
		}

		return $file;
	}

	/**
	 * Performance of image manipulation and save result.
	 *
	 * @param string $file the path to the original image
	 * @param string $newFile path to the resulting image
	 * @param array $params
	 *
	 * @return bool operation status
	 * @throws \Exception
	 */
	private function _doThumbOf( $file, $newFile, $params ) {

		$this->setImage( $file );
		foreach ( $params as $key => $value ) {
			switch ( $key ) {
				case 'resize':
					$this->resize( isset( $value['width'] ) ? $value['width'] : null, isset( $value['height'] ) ? $value['height'] : null, isset( $value['master'] ) ? $value['master'] : null );
				break;
				case 'crop':
					if ( ! isset( $value['width'] ) || ! isset( $value['height'] ) ) {
						throw new \Exception( 'Params "width" and "height" is required for action "' . $key . '"' );
					}
					$this->crop( $value['width'], $value['height'], isset( $value['offset_x'] ) ? $value['offset_x'] : null, isset( $value['offset_y'] ) ? $value['offset_y'] : null );
				break;
				case 'scaleAndCrop':
					$this->scaleAndCrop( $value['width'], $value['height'] );
				break;
				case 'rotate':
					if ( is_array( $value ) ) {
						if ( ! isset( $value['degrees'] ) ) {
							throw new \Exception( 'Param "degrees" is required for action "' . $key . '"' );
						}
						$this->rotate( $value['degrees'] );
					} else {
						$this->rotate( $value );
					}
				break;
				case 'flip':
					if ( is_array( $value ) ) {
						if ( ! isset( $value['direction'] ) ) {
							throw new \Exception( 'Param "direction" is required for action "' . $key . '"' );
						}
						$this->flip( $value['direction'] );
					} else {
						$this->flip( $value );
					}
				break;
				case 'sharpen':
					if ( is_array( $value ) ) {
						if ( ! isset( $value['amount'] ) ) {
							throw new \Exception( 'Param "amount" is required for action "' . $key . '"' );
						}
						$this->sharpen( $value['amount'] );
					} else {
						$this->sharpen( $value );
					}
				break;
				case 'reflection':
					$this->reflection( isset( $value['height'] ) ? $value['height'] : null, isset( $value['opacity'] ) ? $value['opacity'] : 100, isset( $value['fade_in'] ) ? $value['fade_in'] : false );
				break;
				case 'watermark':
					if ( is_array( $value ) ) {
						$this->watermark( isset( $value['watermark'] ) ? $value['watermark'] : null, isset( $value['offset_x'] ) ? $value['offset_x'] : null, isset( $value['offset_y'] ) ? $value['offset_y'] : null, isset( $value['opacity'] ) ? $value['opacity'] : 100 );
					} else {
						$this->watermark( $value );
					}
				break;
				case 'background':
					if ( is_array( $value ) ) {
						if ( ! isset( $value['color'] ) ) {
							throw new \Exception( 'Param "color" is required for action "' . $key . '"' );
						}
						$this->background( $value['color'], isset( $value['opacity'] ) ? $value['opacity'] : 100 );
					} else {
						$this->background( $value );
					}
				break;
				case 'quality':
					if ( ! isset( $value ) ) {
						throw new \Exception( 'Param "' . $key . '" can\'t be empty' );
					}
					$this->quality = $value;
				break;
				case 'type':
				break;
				default:
					throw new \Exception( 'Action "' . $key . '" is not found' );
			}
		}
		$result = $this->save( $newFile, $this->quality );
		@chmod( $newFile, $this->newFileMode );

		return $result;
	}

	/**
	 * This method returns the URL to the cached thumbnail.
	 *
	 * @param string $file path
	 * @param array $params
	 * @param mixed $hash cache version modifier
	 *
	 * @return string URL path
	 */
	public function thumbSrcOf( $file, $params = array(), $hash = null ) {

		// Paths
		$hash          = md5( $file . serialize( $params ) . filemtime( $file ) . (string) $hash );
		$cachePath     = Yii::getAlias( $this->basePath ) . $this->cachePath . $hash{0};
		$cacheFileExt  = isset( $params['type'] ) ? $params['type'] : pathinfo( $file, PATHINFO_EXTENSION );
		$cacheFileName = $hash . '.' . $cacheFileExt;
		$cacheFile     = $cachePath . DIRECTORY_SEPARATOR . $cacheFileName;
		$webCacheFile  = Yii::getAlias( $this->baseUrl ) . $this->cachePath . $hash{0} . '/' . $cacheFileName;

		// Return URL to the cache image
		if ( file_exists( $cacheFile ) && ( time() - filemtime( $cacheFile ) < $this->cacheTime ) ) {
			return $webCacheFile;
		}

		// Make cache dir
		if ( ! is_dir( $cachePath ) ) {
			mkdir( $cachePath, $this->newDirMode, true );
			chmod( Yii::getAlias( $this->basePath ) . $this->cachePath, $this->newDirMode );
			chmod( $cachePath, $this->newDirMode );
		}

		// Create and caching thumbnail use params
		if ( ( $file = $this->detectPath( $file ) ) === false ) {
			return false;
		}
		$image        = Image::factory( $file, $this->driver );
		$originWidth  = $image->width;
		$originHeight = $image->height;
		$result       = $this->_doThumbOf( $image, $cacheFile, $params );
		unset( $image );

		// Same for high-resolution image
		if ( $this->retinaSupport && $result ) {
			foreach ( $this->pixel_ratio as $dppcp ) {
				if ( $this->getImage()->width * $dppcp <= $originWidth && $this->getImage()->height * $dppcp <= $originHeight ) {
					$retinaFile = $cachePath . DIRECTORY_SEPARATOR . $hash . '@' . $dppcp . 'x.' . $cacheFileExt;
					if ( isset( $params['resize']['width'] ) || isset( $params['resize']['height'] ) ) {
						if ( isset( $params['resize']['width'] ) ) {
							$params['resize']['width'] = $this->getImage()->width * $dppcp;
						}
						if ( isset( $params['resize']['height'] ) ) {
							$params['resize']['height'] = $this->getImage()->height * $dppcp;
						}
						$this->_doThumbOf( $file, $retinaFile, $params );
					}
				}
			}
		}

		return $webCacheFile;
	}

	/**
	 * This method returns prepared HTML code for cached thumbnail.
	 * Use standard yii-component CHtml::image().
	 * [Here you'll find srcset explained](https://webkit.org/demos/srcset/ )
	 *
	 * @see https://webkit.org/demos/srcset/
	 *
	 * @param string $file path
	 * @param array $params
	 * @param array $htmlOptions
	 * @param mixed $hash cache version modifier
	 *
	 * @return string HTML
	 */
	public function thumbOf( $file, $params = array(), $htmlOptions = array(), $hash = null ) {

		$img_src = $this->thumbSrcOf( $file, $params, $hash );

		if ( $this->retinaSupport ) {

			$srcset = [ $img_src . ' 1x' ];

			foreach ( $this->pixel_ratio as $dppcp ) {
				if ( ( $dppcp > 1 ) && ( false !== $retina_img_src = $this->getRetinaImgSrc( $img_src, $dppcp ) ) ) {
					$srcset[] = $retina_img_src . ' ' . $dppcp . 'x';
				}
			}

			if ( count( $srcset ) > 1 ) {
				$htmlOptions['srcset'] = implode( ', ', $srcset );
			}

		}

		return Html::img( $img_src, $htmlOptions );
	}

	/**
	 * Checks if retina file is created
	 * E.g. it is not created if source file was too small, etc.
	 *
	 * @param string $thumbSrcOf should be output of thumbSrcOf() method
	 * @param int $dppcp device pixels per CSS pixel
	 *
	 * @return false|string image URL
	 */
	public function getRetinaImgSrc( $thumbSrcOf, $dppcp = 2 ) {

		$file_name_retina = implode( '', [
			pathinfo( $thumbSrcOf, PATHINFO_FILENAME ),
			'@' . $dppcp . 'x.',
			pathinfo( $thumbSrcOf, PATHINFO_EXTENSION )
		] );

		$file_path_retina = implode( '', [
			Yii::getAlias( $this->basePath ) . $this->cachePath,
			$file_name_retina[0] . '/',
			$file_name_retina
		] );

		$img_src = Yii::getAlias( $this->baseUrl ) . $this->cachePath . $file_name_retina[0] . '/' . $file_name_retina;

		return is_file( $file_path_retina ) ? $img_src : false;
	}

	/**
	 * Description of the methods for the AutoComplete feature in a IDE
	 * because it uses a design pattern "factory".
	 */

	public function resize( $width = null, $height = null, $master = null ) {

		return $this->getImage()->resize( $width, $height, $master );
	}

	public function crop( $width, $height, $offset_x = null, $offset_y = null ) {

		return $this->getImage()->crop( $width, $height, $offset_x, $offset_y );
	}

	public function scaleAndCrop( $width, $height ) {

		$this->resize( $width, $height, self::RESIZE_INVERSE );
		$this->crop( $width, $height );
	}

	public function rotate( $degrees ) {

		return $this->getImage()->rotate( $degrees );
	}

	public function flip( $direction ) {

		return $this->getImage()->flip( $direction );
	}

	public function sharpen( $amount ) {

		return $this->getImage()->sharpen( $amount );
	}

	public function reflection( $height = null, $opacity = 100, $fade_in = false ) {

		return $this->getImage()->reflection( $height, $opacity, $fade_in );
	}

	public function watermark( $watermark, $offset_x = null, $offset_y = null, $opacity = 100 ) {

		if ( $watermark instanceof EasyImage ) {
			$watermark = $watermark->getImage();
		} elseif ( is_string( $watermark ) ) {
			$watermark = Image::factory( $this->detectPath( $watermark ), $this->driver );
		}

		return $this->getImage()->watermark( $watermark, $offset_x, $offset_y, $opacity );
	}

	public function background( $color, $opacity = 100 ) {

		return $this->getImage()->background( $color, $opacity );
	}

	public function save( $file = null, $quality = 100 ) {

		return $this->getImage()->save( $file, $quality );
	}

	public function render( $type = null, $quality = 100 ) {

		return $this->getImage()->render( $type, $quality );
	}
}
