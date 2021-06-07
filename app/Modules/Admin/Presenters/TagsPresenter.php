<?php


namespace App\Modules\Admin\Presenters;


use App\Models\TagManager;
use Nette\Application\UI\Form;
use Nette\Database\UniqueConstraintViolationException;
use Nette;
use Tracy\Debugger;

class TagsPresenter extends BaseAdminPresenter
{
    /** @var TagManager @inject */
    public $tagManager;

    protected function createComponentEditTagForm(): Form
    {
        $form = new Form();
        $form->addHidden('slug');
        $form->addText('title', 'Title')
            ->setRequired();
        $form->addSubmit('save', 'Save');
        $form->onSuccess[] = [$this, 'onSuccessEditTagForm'];
        return $form;
    }

    public function onSuccessEditTagForm(Form $form, $values)
    {
        try {
            if ($values['slug']) {
                $this->tagManager->edit($values['slug'], $values['title']);
                $this->flashMessage("Tag {$values['title']} edited.");
            } else {
                $this->tagManager->add($values['title']);
                $this->flashMessage("Tag {$values['title']} added.");
            }
            $this->redirect('default');
        } catch (UniqueConstraintViolationException $e) {
            $form->addError("A tag with the same name already exists.");
        }
    }

    protected function createComponentRemoveTagForm(): Form
    {
        $form = new Form();
        $form->addHidden('slug');
        $form->addSubmit('cancel', 'Cancel');
        $form->addSubmit('remove', 'Confirm remove');
        $form->onSuccess[] = [$this, 'submitRemoveTagForm'];
        return $form;
    }

    public function submitRemoveTagForm(Form $form, $values)
    {
        if ($form['cancel']->isSubmittedBy()) {
            $this->flashMessage('You canceled the removal of the tag.');
        }
        if ($form['remove']->isSubmittedBy()) {
            $this->tagManager->remove($values['slug']);
            $this->flashMessage('The tag is removed.', 'success');
        }
        $this->redirect('default');
    }

    public function actionAdd()
    {
        $this->setView('edit');
    }

    public function actionEdit(string $slug = '')
    {
        $tag = $this->tagManager->get($slug);
        if (!$tag) {
            $this->error('Tag not found.');
        }
        $this->template->tag = $tag;
        $this['editTagForm']->setDefaults($tag);
    }

    public function renderDefault(int $page = 1)
    {
        $tagsCount = $this->tagManager->getAllCount();

        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($tagsCount);
        $paginator->setItemsPerPage(12);
        $paginator->setPage($page);

        $this->template->paginator = $paginator;
        $this->template->tags = $this->tagManager->findAll($paginator->length, $paginator->offset);
        $this->template->tagsCount = $tagsCount;
    }

    public function actionRemove(string $slug = '')
    {
        $tag = $this->tagManager->get($slug);
        if (!$tag) {
            $this->error('Tag not found.');
        }
        $this['removeTagForm']->setDefaults([
            'slug' => $slug
        ]);
        $this->template->tag = $tag;
    }
}