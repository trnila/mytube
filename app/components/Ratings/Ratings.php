<?php
namespace Component\Ratings;
use Nette;
use DateTime;
use Nette\Application\ForbiddenRequestException;
use Model;
use Component;

class Ratings extends Component\BaseControl
{
	/**
	 * @var Model\Ratings
	 * @inject
	 */
	public $ratings;

	/**
	 * @var Model\Entity\Video
	 */
	protected $video;

	public function setVideo(Model\Entity\Video $video)
	{
		$this->video = $video;
	}

	public function handleRate($positive = TRUE, $takeBack = FALSE)
	{
		if(!$this->presenter->user->isLoggedIn()) {
			throw new ForbiddenRequestException;
		}

		$this->video->overallRating = $this->ratings->rate($this->video->id, $this->presenter->user->id, $positive, $takeBack);

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

		$template->positive = $this->video->overallRating->positive;
		$template->negative = $this->video->overallRating->negative;
		$template->total = $this->video->overallRating->total;

		if($this->presenter->user->isLoggedIn()) {
			$rate = $this->ratings->getUserRate($this->video->id, $this->presenter->user->id);

			$template->positiveRate = $rate ? $rate->positive == TRUE : FALSE;
			$template->negativeRate = $rate ? $rate->positive == FALSE : FALSE;
		}

		echo $template;
	}
}