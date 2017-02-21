Image Component for Yii2
========================
Image Component for Yii2

This extension is based on [yii-easyImage](https://github.com/zhdanovartur/yii-easyimage)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require mauglee/yii2-easyimage
```

or add

```
"mauglee/yii2-easyimage": "@dev"
```

to the `require-dev` section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'components' => [
        'easyImage' => [
            'class'         => 'cliff363825\image\EasyImage',
            'driver'        => 'GD',
            'quality'       => 100,
            'cachePath'     => '/easyimage/',
            'cacheTime'     => 2592000,
            'retinaSupport' => true,
            'pixel_ratio'   => [ 2, 3 ], // Device pixels per CSS pixel (retina stuff)
            'basePath'      => '@webroot',
            'baseUrl'       => '@web',
        ]
    ],
];
```
#### Parameters
- string `$file` required - Image file path
- string `$driver` - Driver: `GD`, `Imagick`

### ThumbOf
You can create a thumbnail directly in the `View`:

// Create and autocache
```php
Yii::$app->easyImage->thumbOf('/path/to/image.jpg', ['rotate' => 90]);
```

// or
```php
Yii::$app->easyImage->thumbOf('image.jpg', ['rotate' => 90],  ['class' => 'image']);
```

// or
```php
Yii::$app->easyImage->thumbOf('image.png', [
    'resize' => ['width' => 100, 'height' => 100],
    'rotate' => ['degrees' => 90],
    'sharpen' => 50,
    'background' => '#ffffff',
    'type' => 'jpg',
    'quality' => 60,
  ]);
```
**Note.** This method return [Html::img()](http://www.yiiframework.com/doc-2.0/yii-helpers-basehtml.html)

Example using different filetypes, diferrent images source path:

```php
// in real situation those filenames are retrieved from DB 
$images = [
    '01.jpg',
    '02.jpg',
    '03.png',
];

foreach ( $images as $image ) {
    $file_path = Yii::getAlias( '@app/data/image/logo/' ) . $image;
    if ( is_file( $file_path ) ) {
        echo Yii::$app->easyImage->thumbOf( $file_path, [
            'resize'  => [ 'width' => 300, 'height' => 300 ],
            'type'    => 'jpg',
            'quality' => '86',
        ] );
    }
}
```

#### Parameters
- string `$file` required - Image file path
- array `$params` - Image manipulation methods
- array `$htmlOptions` - options for Html::img()

For full details on usage, see the [documentation on authors page](https://github.com/zhdanovartur/yii-easyimage).
**Note:** Some differencies may occur, because original version is written for Yii v1
