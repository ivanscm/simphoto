<?php


namespace App\Modules\Admin\Presenters;


use App\Models\ImageManager;
use App\Models\TagManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Database\UniqueConstraintViolationException;
use Tracy\Debugger;

class ImagesPresenter extends BaseAdminPresenter
{
    /** @var TagManager $tagManager @inject */
    public $tagManager;

    protected function createComponentImageEditForm(): Form
    {
        $form = new Form();
        $form->addHidden('slug');
        $form->addUpload('image', 'Image')
            ->addRule(Form::IMAGE, 'Image must be JPEG, PNG or GIF')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximum file size is 1Mb.', 12 * 1024 * 1024)
            ->setRequired();
        $form->addText('title', 'Title')
            ->setRequired();
        $form->addMultiSelect('tags', 'Tags', $this->tagManager->findAllForSelect())
            ->setHtmlAttribute('size', 6);
        $form->addTextArea('description', 'Description');
        $form->addSubmit('save', 'Save image');
        $form->onSuccess[] = [$this, 'ImageEditFormSuccess'];
        return $form;
    }

    public function ImageEditFormSuccess(Form $form, $values)
    {
        try {
            if ($values['slug']) {
                $this->imageManager->edit($values['slug'], $values['image'], $values['title'], $values['description'], $values['tags']);
                $this->flashMessage('The image has been edited.');
            } else {
                $this->imageManager->add($values['image'], $values['title'], $values['description'], $values['tags']);
                $this->flashMessage('The image has been added.');
            }
            $this->redirect('default');
        } catch (UniqueConstraintViolationException $e) {
            $form['title']->addError("A image with the same name already exists.");
        }
    }

    protected function createComponentRemoveImageForm(): Form
    {
        $form = new Form();
        $form->addHidden('slug');
        $form->addSubmit('cancel', 'Cancel');
        $form->addSubmit('remove', 'Confirm remove');
        $form->onSuccess[] = [$this, 'submitRemoveImageForm'];
        return $form;
    }

    public function submitRemoveImageForm(Form $form, $values)
    {
        if ($form['cancel']->isSubmittedBy()) {
            $this->flashMessage('You canceled the removal of the image.');
        }
        if ($form['remove']->isSubmittedBy()) {
            $this->imageManager->remove($values['slug']);
            $this->flashMessage('The image is removed.', 'success');
        }
        $this->redirect('default');
    }

    public function renderDefault(int $page = 1)
    {
        $imagesCount = $this->imageManager->getAllCount();

        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($imagesCount);
        $paginator->setItemsPerPage(10);
        $paginator->setPage($page);

        $this->template->paginator = $paginator;
        $this->template->images = $this->imageManager->findAll($paginator->length, $paginator->offset);
        $this->template->imagesCount = $imagesCount;
    }

    public function actionAdd()
    {
        $this->setView('edit');
    }

    public function actionEdit(string $slug = '')
    {
        $image = $this->imageManager->get($slug);
        if (!$image) {
            $this->error('Image not found.');
        }
        $this->template->image = $image;
        /** @var Form $form */
        $form = $this['imageEditForm'];
        $form->setDefaults($image);
        $form['image']->setRequired(false);
        $form['tags']->setDefaultValue($this->imageManager->findTags($image['slug']));
    }

    public function actionRemove(string $slug = '')
    {
        $image = $this->imageManager->get($slug);
        if (!$image) {
            $this->error('Image not found.');
        }
        $this['removeImageForm']->setDefaults([
            'slug' => $slug
        ]);
        $this->template->image = $image;
    }
}