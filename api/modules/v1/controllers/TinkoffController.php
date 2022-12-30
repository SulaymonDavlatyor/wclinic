<?php

namespace api\modules\v1\controllers;


use Yii;
use yii\web\Controller;
use common\models\TinkoffPrePay;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use common\components\payments\tinkoff\TinkoffPay;
use common\components\payments\tinkoff\exceptions\HttpException;

class TinkoffController extends Controller
{
	public $enableCsrfValidation = false;

	protected $token = 'OBlzKMheJOz47TsC';

	public function actionIndex($token)
	{
		if ($token !== $this->token) {
			throw new BadRequestHttpException();
		}

//		Yii::error(Yii::$app->request->post(), 'tinkoff_data');

		$status = Yii::$app->request->post('Status');

		if ($status !== 'CONFIRMED' && $status !== 'REJECTED') {
			Yii::$app->end();
		}

		$paymentId = Yii::$app->request->post('PaymentId');
		$orderId = Yii::$app->request->post('OrderId');


		$tinkoffPrePay = TinkoffPrePay::find()
			->where(['payment_id' => $paymentId])
			->andWhere(['order_id' => $orderId])
			->one();

		if (is_null($tinkoffPrePay)) {
			throw new NotFoundHttpException();
		}


		$tinkoffPrePay->setAttribute('status', $status);

		if ($status === 'CONFIRMED') {
			$this->writeGoogleSheet($tinkoffPrePay);
		}

		if ($status === 'CONFIRMED') {
            $tinkoffPrePay->setAttribute('order_id', Yii::$app->rnova->handleAppointment($orderId));
        }
		else {
            Yii::$app->rnova->cancelAppointment($orderId, 'Платеж не прошел');
        }
		$tinkoffPrePay->save();

        echo 'OK';
        exit();
	}

	public function actionTest()
	{
////	    var_dump(Yii::$app->rnova->getAppointment(4653221));
//        exit();
        $tinkoffPrePay = TinkoffPrePay::find()
            ->andWhere(['order_id' => 4661604])
            ->one();
        var_dump($this->writeGoogleSheet($tinkoffPrePay));
        exit();
//		$url = Yii::$app->urlManager->createUrl()
//		Yii::$app->rnova->getAppointment(4653221);
//		Yii::$app->rnova->confirmAppointment(4653221, 'Задаток поступил');
		exit();
		/** @var TinkoffPay $paymentService */
		$paymentService = Yii::$app->tinkoffPay;

		$paymentRequest = $paymentService->initPay(6, 1000 * 100);
		$date = new \DateTime();
		$date->modify("+30 minutes");
		$paymentRequest->setRedirectDueDate($date);

//		var_dump($paymentRequest->send());
//		exit();


		try {
			$response = $paymentRequest->send();
		} catch (HttpException $exception) {
			throw new \yii\web\HttpException($exception->statusCode, $exception->getMessage());
		}

		$tinkoffPrePay = new TinkoffPrePay();
		$tinkoffPrePay->status = $response->getStatus();
		$tinkoffPrePay->order_id = $response->getOrderId();
		$tinkoffPrePay->payment_id = $response->getPaymentId();
		$tinkoffPrePay->due_datetime = $date->format('Y-m-d H:i:s');
		$tinkoffPrePay->save();

		var_dump($response->getPaymentUrl());
//		var_dump($tinkoffPrePay);
		exit();
	}

	protected function writeGoogleSheet(TinkoffPrePay $tinkoffPrePay)
    {
        // Путь к файлу ключа сервисного аккаунта
        $googleAccountKeyFilePath = __DIR__ . '/credentials.json';
        putenv( 'GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath );

        // Документация https://developers.google.com/sheets/api/
        $client = new \Google_Client();
        $client->useApplicationDefaultCredentials();

        // Области, к которым будет доступ
        // https://developers.google.com/identity/protocols/googlescopes
        $client->addScope( 'https://www.googleapis.com/auth/spreadsheets' );

        $service = new \Google_Service_Sheets( $client );

        // ID таблицы
        $spreadsheetId = '1yeHu8V0BQ889EUchcKVa3PH-ZHOq7UKn0XFGfD0vyA4';

        $response = $service->spreadsheets_values->get($spreadsheetId, 'Count!A1');

        $values = $response->getValues();
        $total = $values[0][0];
        $row = $total + 2;

        $update_range = "List1!A$row:E$row";


        $appointment = Yii::$app->rnova->getAppointment($tinkoffPrePay->order_id);
//        $appointment = Yii::$app->rnova->getAppointment(4653221);

        $values = [[
            $appointment['patient_name'], //ФИО
            $appointment['date_created'], //Дата Записи
            $appointment['time_start'], //дата приёма
            $appointment['patient_phone'], //номер телефона
            $tinkoffPrePay->order_id, //идентификатор операции из Эквайринга
        ]];
        $body = new \Google_Service_Sheets_ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'RAW'];
        $update_sheet = $service->spreadsheets_values->update($spreadsheetId, $update_range, $body, $params);

        $body = new \Google_Service_Sheets_ValueRange(['values' => [[$total + 1]]]);
        $update_sheet = $service->spreadsheets_values->update($spreadsheetId, 'Count!A1', $body, $params);

    }
}
