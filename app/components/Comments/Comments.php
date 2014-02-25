<?php
namespace Component\Comments;
use Nette;
use DateTime;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\BadRequestException;
use Model;
use Component;

class Comments extends Component\BaseControl
{
	/**
	 * @var Model\Comments
	 * @inject
	 */
	public $comments;

	/**
	 * @var Model\Entity\Video
	*/
	protected $video;

	public function setVideo(Model\Entity\Video $video)
	{
		$this->video = $video;
	}

	public function handleDelete($id)
	{
		$comment = $this->comments->find($id);

		if(!$comment) {
			throw new BadRequestException;
		}

		if(!$this->presenter->user->isAllowed($comment, 'delete')) {
			throw new ForbiddenRequestException;
		}

		$this->comments->delete($comment->id);
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

		$template->comments = $this->comments->findAllOrderedByDate($this->video->id);

		echo $template;
	}

	public function addComment($form)
	{
		if(!$this->presenter->user->isLoggedIn()) {
			throw new ForbiddenRequestException;
		}

		$comment = new Model\Entity\Comment;
		$comment->user_id = $this->presenter->user->id;
		$comment->text = $form['text']->value;
		$comment->created = new DateTime;

		$this->comments->addComment($this->video->id, $comment);

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