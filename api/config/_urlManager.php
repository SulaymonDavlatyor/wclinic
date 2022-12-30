<?php
return [
    'class' => 'yii\web\UrlManager',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        // Api
        ['class' => 'yii\rest\UrlRule', 'controller' => 'api/v1/article', 'only' => ['index', 'view', 'options']],
        ['class' => 'yii\rest\UrlRule', 'controller' => 'api/v1/page', 'only' => ['view']],
//	 	'/exchanger/<controller:\w+>/<action:\w+>/<id:\d+>' => '/exchanger/<controller:\w+>/<action:\w+>',
//	 	'/exchanger/<controller:\w+>/<action:\w+>' => '/exchanger/<controller:\w+>/<action:\w+>',
	 	'<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => '<module>/<controller>/<action>',
	 	'<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>' => '<module>/<controller>/<action>',
//		'v1' =>  '/v1/main/index'
    ]
];
