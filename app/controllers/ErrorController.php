<?php

require_once 'Controller.php';
require_once APP . '/models/User.php';


class ErrorController extends Controller
{
    public function actionIndex()
    {
        $userInfo = User::getUserInfoByCookie();

        $viewData['errorMessage'] = '404 - страницы не существует';

        if ($userInfo['authorized']) {
            $viewData['login'] = $userInfo['login'];
            $this->view->render('error_admin', $viewData);
        } else {
            $this->view->render('error', $viewData);
        }
    }
}

