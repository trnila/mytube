<?php
namespace Component;
use Nette, DateTime, Nette\Application\ForbiddenRequestException, Nette\Application\BadRequestException, ActiveRow;

class Comments extends BaseControl
{
	protected $video;

	public function setVideo(ActiveRow\Video $video)
	{
		$this->video = clone $video;
	}

	public function handleDelete($id)
	{
		$comment = $this->video
			->related('comment')
			->find($id)
			->fetch();

		if(!$comment) {
			throw new BadRequestException;
		}

		if(!$this->presenter->user->isAllowed($comment, 'delete')) {
			throw new ForbiddenRequestException;
		}

		$comment->delete();
		$this->flashMessage('Váš komentář byl smazán.', 'success');

		if($this->presenter->isAjax()) {
			$this->invalidateControl('comments');
		}
		else {
			$this->redirect('this');
		}
	}

	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/comments.latte');

		$template->comments = $this->video->related('comments')->order('created DESC');

		echo $template;
	}

	public function addComment($form)
	{
		if(!$this->presenter->user->isLoggedIn()) {
			throw new ForbiddenRequestException;
		}

		$this->video->related('comments')
			->insert(array(
				'user_email' => $this->presenter->user->id,
				'created' => new DateTime,
				'text' => $form['text']->value,
				'name' => 'test'
			));

		$this->flashMessage('Komentář byl přidán.', 'success');
		
		if($this->presenter->isAjax()) {
			$this->invalidateControl('comments');

			$this['form']['text']->value = '';
			$this->invalidateControl('form');
		}
		else {
			$this->redirect('this');
		}
	}

	public function createComponentForm()
	{
		$form = $this->presenter->createForm();

		$form->addTextArea('text')
			->setRequired();

		$form->addSubmit('add', 'Přidat komentář');

		$form->onSuccess[] = array($this, 'addComment');

		return $form;
	}
}