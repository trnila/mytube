<?php
namespace Component;
use Nette, DateTime, \Nette\Application\ForbiddenRequestException;

class Ratings extends BaseControl
{
	protected $video;

	public function setVideo(Nette\Database\Table\ActiveRow $video)
	{
		$this->video = clone $video;
	}

	public function handleRate($positive = TRUE, $takeBack = FALSE)
	{
		if(!$this->presenter->user->isLoggedIn()) {
			throw new ForbiddenRequestException;
		}

		// TODO: workaround, because of this issue https://github.com/nette/nette/pull/799
		// $this->video->related('ratings')->where('user_id', $this->presenter->user->id)->delete();
		$this->presenter->context->getByType('Nette\Database\Context')->table('video_ratings')
			->where('user_id', $this->presenter->user->id)
			->where('video_id', $this->video->id)
			->delete();

		if(!$takeBack) {
			$this->video->related('ratings')
				->insert(array(
					'positive' => (bool) $positive,
					'user_id' => $this->presenter->user->id,
					'created' => new DateTime
				));
		}

		if($this->presenter->isAjax()) {
			$this->invalidateControl();
		}
		else {
			$this->redirect('this');
		}
	}

	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/ratings.latte');

		$template->positive = $this->video->related('ratings')->where('positive', TRUE)->count('*');
		$template->negative = $this->video->related('ratings')->where('positive', FALSE)->count('*');
		$template->total = $template->positive + $template->negative;

		if($this->presenter->user->isLoggedIn()) {
			$rate = $this->video->related('ratings')->where('user_id', $this->presenter->user->id)->fetch();

			$template->positiveRate = $rate ? $rate->positive == TRUE : FALSE;
			$template->negativeRate = $rate ? $rate->positive == FALSE : FALSE;
		}

		echo $template;
	}
}