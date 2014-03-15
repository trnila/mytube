<?php
namespace Model;
use Nette;
use DateTime;

class Ratings extends Repository
{
	/** @var string table name */
	protected $tableName = 'video_ratings';


	/**
	 * Gets user's rate of file
	 * @param $video_id int
	 * @param $user_id int
	 * @return Model\Entity\Rating|NULL
	 */
	public function getUserRate($video_id, $user_id)
	{
		$rate = $this->findAll()
			->where('video_id', $video_id)
			->where('user_id', $user_id)
			->fetch();

		return $rate ? Entity\Rating::create($rate) : NULL;
	}

	/**
	 * Rates or takes rate back in video
	 * @param $video_id int
	 * @param $user_id int
	 * @param $positive bool
	 * @param $takeBack bool if rate has been taken back
	 */
	public function rate($video_id, $user_id, $positive, $takeBack)
	{
		$actualRate = $this->findAll()
			->where('video_id', $video_id)
			->where('user_id', $user_id);

		$actualRate->delete();

		if(!$takeBack) {
			$this->create(array(
				'video_id' => $video_id,
				'user_id' => $user_id,
				'positive' => (bool) $positive,
				'created' => new DateTime
			));
		}

		$result = new Entity\OverallRating;
		$result->positive = $this->findAll()->where('video_id', $video_id)->where('positive', TRUE)->count();
		$result->negative = $this->findAll()->where('video_id', $video_id)->where('positive', FALSE)->count();

		return $result;
	}
}
