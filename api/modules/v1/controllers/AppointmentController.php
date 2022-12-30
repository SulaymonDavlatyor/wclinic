<?php

namespace api\modules\v1\controllers;

use common\models\Diagnosis;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use common\models\Special;
use yii\helpers\ArrayHelper;
use common\models\VisitType;
use common\models\RnovaPrice;
use api\modules\v1\resources\Doctor;
use common\models\RnovaPriceCategory;

class AppointmentController extends Controller
{
	public function beforeAction($action)
	{
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");

		Yii::$app->response->format = Response::FORMAT_JSON;

		return parent::beforeAction($action); // TODO: Change the autogenerated stub
	}
	
    public function actionSpecials()
    {
        $query = Special::find();

        $sortBy = $this->request->get('sortBy');

        if ($sortBy) {
            $query->orderBy("$sortBy ASC");
        }

        return $query->all();
    }
    public function actionSpecial($id)
    {
        $query = Special::find()->where(['id'=>$id]);

      // $sortBy = $this->request->get('sortBy');

      // if ($sortBy) {
      //     $query->orderBy("$sortBy ASC");
      // }

        return $query->all();
    }
    public function actionAwait()
    {
$request = Yii::$app->request;
        return $request->getBodyParam('client_fname');

      // $sortBy = $this->request->get('sortBy');

      // if ($sortBy) {
      //     $query->orderBy("$sortBy ASC");
      // }

        return $query->all();
    }
    public function actionDiagnosis()
    {

        $query = Diagnosis::find();

      // $sortBy = $this->request->get('sortBy');

      // if ($sortBy) {
      //     $query->orderBy("$sortBy ASC");
      // }

        return $query->all();
    }
    public function actionDiagnos($id)
    {

        $query = Diagnosis::find()->where(['id'=>$id]);

      // $sortBy = $this->request->get('sortBy');

      // if ($sortBy) {
      //     $query->orderBy("$sortBy ASC");
      // }

        return $query->all();
    }

	public function actionPrices()
	{
		$special_id = $this->request->get('special_id');
        $result = [];

		if ($special_id) {
            $categoriesQuery = RnovaPriceCategory::find()
                ->joinWith(['prices p', 'prices.specials ps'])
                ->with('prices')
                ->andWhere('parent_id is not null')
                ->andWhere('p.id is not null')
			    ->andWhere(['ps.id' => $special_id])
                ->orderBy('sort ASC');

            $categories = $categoriesQuery->all();

            /**
             * @var $categories RnovaPriceCategory[]
             */
            foreach ($categories as $category) {
                $prices = $category->prices;

                foreach ($prices as $index => $price) {
                    if (!is_null($special_id) && !in_array($special_id, $price->special_ids)) {
                        unset($prices[$index]);
                    }
                }

                $cat = [
                    'id' => $category->id,
                    'children' => [],
                    'title' => $category->title,
                    'services' => $prices,
                ];

                $result[] = $cat;
            }
		}
        else {
            $categoriesQuery = RnovaPriceCategory::find()
                ->with(['children', 'children.prices'])
                ->andWhere('parent_id is null')
                ->orderBy('sort ASC');

            $categories = $categoriesQuery->all();

            /**
             * @var $categories RnovaPriceCategory[]
             */
            foreach ($categories as $category) {
                $prices = [];

                foreach ($category->children as $child) {
                    $prices = array_merge($prices, $child->prices);
                }

                $cat = [
                    'id' => $category->id,
                    'children' => [],
                    'title' => str_replace('.', '', $category->title),
                    'services' => $prices,
                ];

                $result[] = $cat;
            }
        }

		return $result;
	}

	public function actionVisitTypeDoctors()
	{
		$visit_type = $this->request->get('visit_type');

		$visitType = VisitType::find()->with(['professions', 'professions.doctors'])->where(['id' => $visit_type])->one();

		/**
		 * @var $visitTypeDoctors Doctor[]
		 */
		$visitTypeDoctors = [];
		foreach ($visitType->professions as $profession) {
			$visitTypeDoctors = ArrayHelper::merge($visitTypeDoctors, $profession->doctors);
		}

		$result = [];

		foreach ($visitTypeDoctors as $_i => $visitTypeDoctor) {
			if (!empty($visitType->doctor_ids) && !in_array($visitTypeDoctor->id, $visitType->doctor_ids)) {
				unset($visitTypeDoctors[$_i]);
				continue;
			}

			$doctor = new \stdClass();
			$doctor->name = $visitTypeDoctor->name;
			$doctor->external_id = $visitTypeDoctor->external_id;
			$doctor->avatar = $visitTypeDoctor->avatar;

			$result[] = $doctor;
		}

		return $result;
	}
}
