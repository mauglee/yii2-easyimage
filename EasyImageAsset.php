<?php
namespace cliff363825\image;

use yii\web\AssetBundle;

/**
 * Class EasyImageAsset
 * @package cliff363825\image
 */
class EasyImageAsset extends AssetBundle
{
    public $sourcePath = '@cliff363825/image/assets';
    public $js = [
        'retina.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}