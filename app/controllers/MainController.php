<?php

namespace EvgputWeb\MVC\Controllers;


class MainController extends Controller
{
    public function actionIndex()
    {
        $userInfo = self::getUserInfoByCookie();

        $viewData = ['curSection' => ''];

        if ($userInfo['authorized']) {
            $viewData['login'] = $userInfo['login'];
            $viewData['name'] = $userInfo['name'];
        }

        $this->view->render('main', $viewData);
    }
}
