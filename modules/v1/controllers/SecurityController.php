<?php

namespace app\modules\v1\controllers;

use app\models\AccessTokens;
use app\models\User;
use app\modules\v1\controllers\base\BaseController;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\modules\v1\models\LoginForm;
use app\modules\v1\models\PasswordResetRequestForm;
use app\modules\v1\models\ResetPasswordForm;
use app\modules\v1\models\SignupForm;

/**
 * Site controller
 */
class SecurityController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return ArrayHelper::merge($behaviors, [
            'authenticator' => [
                'optional' => [
                    'login',
                    'verify-auth'
                ],
            ],
        ]);
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        //When you're logged stop the procedure
        if (!Yii::$app->user->isGuest) {
            return [
                'error' => 'already-logged-in'
            ];
        }

        //Instance Login Model
        $model = new LoginForm();

        //Loading data to validate
        $model->username = Yii::$app->request->post('username');
        $model->password = Yii::$app->request->post('password');

        //Device info
        $tokenDevice = Yii::$app->request->post("token");
        $osDevice = Yii::$app->request->post("os");

        //Validate submitted data and try to login
        if ($model->validate()) {
            //Get user record
            $User = User::findByUsername($model->username);

            //Generate access token
            $User->refreshAccessToken($tokenDevice, $osDevice);

            //Store with new access token
            $User->save();

            //User informations
            $result = $User->toArray(
                [
                    'id',
                    'username',
                    'email',
                    'accessToken',
                    'fcmToken',
                ]
            );

            return $result;
        } else {
            //Your data is wrong
            return [
                'error' => $model->getErrors()
            ];
        }
    }

    public function actionVerifyAuth() {
        $bodyParams = \Yii::$app->getRequest()->getBodyParams();

        if($bodyParams['token']) {
            $token = AccessTokens::findOne(['access_token' => $bodyParams['token']]);

            if($token && $token->access_token) {
                return [
                    'status' => true
                ];
            }
        }

        return [
            'error' => true,
            'error-message' => 'Token Non valido'
        ];
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}
