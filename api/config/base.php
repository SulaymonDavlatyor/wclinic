<?php
return [
	'id' => 'frontend',
	'basePath' => dirname(__DIR__),
	'components' => [
		'urlManager' => require(__DIR__ . '/_urlManager.php'),
		'cache' => require(__DIR__ . '/_cache.php'),
		'log' => [
			'targets' => [
				[
					'class' => 'yii\log\DbTarget',
					'levels' => ['error', 'warning'],
				],
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
	],
];
