<?php
namespace Component;
use Nette, DateTime;

class Comments extends BaseControl
{
	protected $video;

	public function setVideo(Nette\Database\Table\ActiveRow $video)
	{
		$this->video = clone $video;
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
		$this->video->related('comments')
			->insert(array(
				'user_id' => $this->presenter->user->id,
				'created' => new DateTime,
				'text' => $form['text']->value,
				'name' => 'test'
			));

		$this->flashMessage('Komentář byl přidán.', 'success');
		$this->redirect('this');
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