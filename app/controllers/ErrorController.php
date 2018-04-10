<?php

namespace EvgputWeb\MVC\Controllers;


class ErrorController extends Controller
{
    public function actionIndex()
    {
        $userInfo = self::getUserInfoByCookie();

        $viewData['errorMessage'] = '404 - страницы не существует';

        if ($userInfo['authorized']) {
            $viewData['login'] = $userInfo['login'];
            $this->view->render('error_admin', $viewData);
        } else {
            $this->view->render('error', $viewData);
        }
    }
}
