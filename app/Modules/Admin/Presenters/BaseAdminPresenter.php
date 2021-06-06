<?php


namespace App\Modules\Admin\Presenters;

use App\Presenters\BasePresenter;
use App\Services\ParameterService;
use Nette\Application\UI\Presenter;

class BaseAdminPresenter extends BasePresenter
{
    public function startup()
    {
        parent::startup();
        if (!$this->getUser()->isLoggedIn() && $this->getPresenter()->name !== 'Admin:Sign') {
            $this->flashMessage('Please login using your login credentials.');
            $this->redirect(':Admin:Sign:in');
        }
    }
}