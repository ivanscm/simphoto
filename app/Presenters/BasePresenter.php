<?php


namespace App\Presenters;


use App\Models\ImageManager;
use App\Services\ParameterService;

class BasePresenter extends \Nette\Application\UI\Presenter
{
    /** @var ParameterService @inject */
    public $parameterService;

    /** @var ImageManager $imageManager @inject */
    public $imageManager;

    public function startup()
    {
        parent::startup();
        $this->layout = $this->parameterService->getAppDir() . '/Presenters/templates/@layout.latte';
        $this->template->addFunction('imageUrl', [$this->imageManager, 'imageUrl']);
        $this->template->site_name = $this->parameterService->getSiteName();
    }
}