<?php


namespace App\Presenters;


use App\Services\ParameterService;

class BasePresenter extends \Nette\Application\UI\Presenter
{
    /** @var ParameterService @inject */
    public $parameterService;

    public function startup()
    {
        parent::startup();
        $this->layout = $this->parameterService->getAppDir() . '/Presenters/templates/@layout.latte';
        $this->template->site_name = $this->parameterService->getSiteName();
    }
}