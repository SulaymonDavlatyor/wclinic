<?php

if (YII_ENV_DEV) {
	$cache = [
		'class' => 'yii\redis\Cache',
		'redis' => [
			'hostname' => 'redis',
			'password' => '/]#jF%wS7nFz]g2^',
		],
	];
}
else {
	$cache = [
		'class' => 'yii\redis\Cache',
		'redis' => [
			'hostname' => 'redis',
			'password' => '/]#jF%wS7nFz]g2^',
		],
	];
}



return $cache;
