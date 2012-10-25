<?php

class HomepagePresenter extends BasePresenter
{
	public function renderDefault()
	{
		$this->template->videos = $this->context->database->table('videos');
	}

	public function createComponentUpload()
	{
		$form = $this->createForm();

		$form->addUpload('video');

		$form->addSubmit('ff');
		$form->onSuccess[] = array($this, 'upload');

		return $form;
	}

	public function upload($form)
	{
		$ch = $this->context->queue->channel();


		$video = $this->context->database->table('videos')->insert(array(
			'id' => Nette\Utils\Strings::random(8, 'a-z0-9A-Z'),
			'title' => 'bla bla bla',
			'description' => 'blablabla',
			'created' => new DateTime,
			'processed' => 0,
			'user_email' => 'admin@example.com'
		));


		$form['video']->value->move("../incoming/{$video['id']}");


		$ch->queue_declare('proccessVideo', false, true, false, false);
		$msg_body = "{$video['id']}";
		$msg = new PhpAmqpLib\Message\AMQPMessage($msg_body, array('content_type' => 'text/plain'));
		$ch->basic_publish($msg, "", "proccessVideo");
		exit;
	}
}
