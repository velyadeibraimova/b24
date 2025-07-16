<?php

namespace Otus\Controllers\Doctors;

use Bitrix\Main\LoaderException;
use \Otus\Models\Doctors\Model;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Context;

class Controller
{
    private $model = null;
    private $app;

    public function __construct()
    {
        global $APPLICATION;
        $this->app = $APPLICATION;
        $this->model = new Model();
    }

    /**
     * @return void
     */
    public function index()
    {
        $this->app->SetTitle(Loc::getMessage('INDEX_TITLE'));
        $doctors = $this->model->getAll();
        include_once __DIR__ . '/../../Views/Doctors/index.php';
    }

    /**
     * @param $slug
     * @return void
     */
    public function show($slug)
    {
        $doctor = $this->model->getDoctor($slug);
        if ($doctor) {
            $title = $doctor['LASTNAME'] . ' ' . $doctor['FIRSTNAME'] . ' ' . $doctor['PATRONYMIC'];
            $this->app->SetTitle($title);
            include_once __DIR__ . '/../../Views/Doctors/show.php';
        }
    }

    /**
     * @return void
     */
    public function add()
    {
        $this->app->SetTitle(Loc::getMessage('ADD'));
        $procedures = $this->model->getAllProcedures();
        include_once __DIR__ . '/../../Views/Doctors/add.php';
    }

    /**
     * @param $slug
     * @return void
     */
    public function edit($slug)
    {
        $doctor = $this->model->getDoctor($slug);
        if ($doctor) {
            $title = Loc::getMessage('EDIT') . ': ' . $doctor['LASTNAME'] . ' ' . $doctor['FIRSTNAME'] . ' ' . $doctor['PATRONYMIC'];
            $this->app->SetTitle($title);
            $procedures = $this->model->getAllProcedures();
            include_once __DIR__ . '/../../Views/Doctors/edit.php';
        }
    }

    /**
     * @return void
     */
    public function create()
    {
        $request = Context::getCurrent()->getRequest();
        $data = $request->getPostList()->toArray();

        $data['SLUG'] = htmlspecialchars($data['SLUG']);
        $data['LASTNAME'] = htmlspecialchars($data['LASTNAME']);
        $data['FIRSTNAME'] = htmlspecialchars($data['FIRSTNAME']);
        $data['PATRONYMIC'] = htmlspecialchars($data['PATRONYMIC']);
        $this->model->add($data);
        LocalRedirect("/doctors/");
    }

    /**
     * @return void
     * @throws LoaderException
     */
    public function update()
    {
        $request = Context::getCurrent()->getRequest();
        $data = $request->getPostList()->toArray();
        if (isset($data['ID']) && (int)$data['ID'] > 0) {
            $data['ID'] = (int)$data['ID'];
            $data['SLUG'] = htmlspecialchars($data['SLUG']);
            $data['LASTNAME'] = htmlspecialchars($data['LASTNAME']);
            $data['FIRSTNAME'] = htmlspecialchars($data['FIRSTNAME']);
            $data['PATRONYMIC'] = htmlspecialchars($data['PATRONYMIC']);
            $this->model->update($data);
            LocalRedirect("/doctors/" . $data['SLUG']);
        }
    }

    /**
     * @return void
     */
    public function addProc()
    {
        $this->app->SetTitle(Loc::getMessage('ADD_PROC'));
        include_once __DIR__ . '/../../Views/Doctors/addproc.php';
    }

    /**
     * @return void
     */
    public function createProc()
    {
        $request = Context::getCurrent()->getRequest();
        $data = $request->getPostList()->toArray();

        $data['NAME'] = htmlspecialchars($data['NAME']);
        $this->model->addProc($data);
        LocalRedirect("/doctors/");
    }
}