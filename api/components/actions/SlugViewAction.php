<?php

namespace api\components\actions;

use yii\rest\ViewAction;
use yii\web\HttpException;
use yii\db\ActiveRecordInterface;

class SlugViewAction extends ViewAction
{
	/**
	 * Displays a model.
	 * @param string $slug the primary key of the model.
	 * @return \yii\db\ActiveRecordInterface the model being displayed
	 * @throws HttpException
	 */
    public function run($slug)
    {
        $model = $this->findModel($slug);
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        return $model;
    }

	/**
	 * @param $slug
	 * @return array|null|\yii\db\ActiveRecord
	 * @throws HttpException
	 */
	public function findModel($slug)
	{
		/* @var $modelClass ActiveRecordInterface */
		$modelClass = $this->modelClass;

		$model = $modelClass::find()
			->where(['slug' => $slug])
			->one();
		if (!$model) {
			throw new HttpException(404);
		}
		return $model;
	}
}
