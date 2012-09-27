<?php
namespace Component;
use Nette, DateTime;

class Ratings extends BaseControl 
{
	protected $video;

	public function setVideo(Nette\Database\Table\ActiveRow $video)
	{
		$this->video = clone $video;
	}

	public function handleRate($positive = true)
	{
		$this->video->related('ratings')
			->where('user_id', $this->presenter->user->id)
			->delete();

		$this->video->related('ratings')
			->insert(array(
				'positive' => (bool) $positive,
				'user_id' => $this->presenter->user->id,
				'created' => new DateTime
			));
	}

	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/ratings.latte');

		$template->positive = $this->video->related('ratings')->where('positive', true)->count('*');
		$template->negative = $this->video->related('ratings')->where('positive', false)->count('*');
		$template->total = $template->positive + $template->negative;

		$rate = $this->video->related('ratings')->where('user_id', $this->presenter->user->id)->fetch();

		$template->positiveRate = $rate ? $rate->positive == true : false;
		$template->negativeRate = $rate ? $rate->positive == false : false;

		echo $template;
	}	
}