<?php


namespace App\Modules\Admin\Presenters;


use App\Models\ImageManager;
use App\Models\TagManager;
use Nette\Application\UI\Form;
use Nette\Database\UniqueConstraintViolationException;

class ImagesPresenter extends BaseAdminPresenter
{
    /** @var TagManager $tagManager @inject */
    public $tagManager;

    /** @var ImageManager $imageManager @inject */
    public $imageManager;

    protected function createComponentAddImageForm(): Form
    {
        $form = new Form();
        $form->addUpload('image', 'Image')
            ->addRule(Form::IMAGE, 'Image must be JPEG, PNG or GIF')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximum file size is 1Mb.', 12 * 1024 * 1024)
            ->setRequired();
        $form->addText('title', 'Title')
            ->setRequired();
        $form->addMultiSelect('tags', 'Tags', $this->tagManager->findAllForSelect())
            ->setHtmlAttribute('size', 6);
        $form->addTextArea('description', 'Description');
        $form->addSubmit('save', 'Add image');
        $form->onSuccess[] = [$this, 'addImageFormSuccess'];
        return $form;
    }

    public function addImageFormSuccess(Form $form, $values)
    {
        try {
            $this->imageManager->add($values['image'], $values['title'], $values['description'], $values['tags']);
        } catch (UniqueConstraintViolationException $e) {
            $form['title']->addError("A image with the same name already exists.");
        }
    }
}