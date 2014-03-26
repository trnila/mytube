<?php
namespace Model;
use Nette\Utils\Validators;
use Nette;
use InvalidArgumentException;

class Videos extends Repository
{
	/** @var string location of incoming videos waiting for process */
	public $incomingDir;

	/** @var string location of media directory containing videos/ and thumbnails/ directory */
	public $mediaDir;

	/** @var string table name */
	protected $tableName = 'videos';


	/**
	 * Finds video by id
	 * @param $id string
	 * @return Model\Entity\Video|FALSE
	 */
	public function find($id)
	{
		$row = parent::find($id);

		return $row ? Entity\Video::create($row) : FALSE;
	}

	public function addVideoToProcess(Entity\Video $video, Nette\Http\FileUpload $file)
	{
		$incomingFilePath = NULL;
		for($tries = 3; $tries > 0; $tries--) {
			try {
				// try to generate id
				$video->id = Nette\Utils\Strings::random(8, 'a-z0-9A-Z');

				// video with same ID already exists, skipping
				if($this->find($video->id)) {
					continue;
				}

				$row = $this->create(array(
					'id' => $video->id,
					'title' => $video->title,
					'description' => $video->description,
					'created' => $video->created,
					'user_id' => $video->user_id,
				));

				foreach($video->tags as $tag) {
					$this->addTag($video->id, $tag['tag'], $tag['position']);
				}

				// save file to incoming location for further process
				$incomingFilePath = $this->incomingDir . "/{$video->id}";
				$file->move($incomingFilePath);

				// send video to queue
				$client = new \GearmanClient;
				$client->addServer();
				$jobId = $client->doBackground("processVideo", $video->id);

				$this->update($row->id, array(
					'jobid' => $jobId
				));

				return $row;
			}
			catch(\PDOException $e) {
				// re-throw just if its not unique-or-another-constraint exception
				if($e->getCode() != 23000) {
					throw $e;
				}
			}
		}

		// delete uploaded file if exists
		if($incomingFilePath && file_exists($incomingFilePath)) {
			@unlink($incomingFilePath);
		}

		throw new Exception("Could not create a video!");
	}

	public function create($data)
	{
		// work arround, because Nette\Database wont refetch the inserted value, because there is no return of last_insert_id
		parent::create($data);
		return $this->find($data['id']);
	}

	public function update($id, $data)
	{
		$video = parent::find($id);

		if(isset($data['tags'])) {
			$tags = $video->related('video_tags')->delete();

			$position = 0;
			foreach($data['tags'] as $tag) {
				$video->related('video_tags')->insert(array(
					'position' => $position,
					'tag' => $tag
				));
				$position++;
			}

			unset($data['tags']);
		}


		$video->update($data);
	}

	public function addTag($id, $tag, $position)
	{
		$this->getTable('video_tags')
			->insert(array(
				'video_id' => $id,
				'tag' => $tag,
				'position' => $position
			));
	}

	public function addThumbnail(Entity\Video $video, $number, $time)
	{
		$this->getTable('video_thumbnails')
			->insert(array(
				'video_id' => $video->id,
				'number' => $number,
				'time' => $time
			));
	}

	public function deleteVideo(Entity\Video $video)
	{
		// delete file in database
		$this->getTable()->wherePrimary($video->id)->delete();

		$file = $this->mediaDir . '/' . $video->getLocation();
		if(!file_exists($file) || !@unlink($file)) {
			trigger_error("Video could not be removed: " . $file, E_USER_NOTICE);
		}

		foreach($video->thumbnails as $thumbnail) {
			$file = $this->mediaDir . '/' . $thumbnail->getLocation();
			if(!file_exists($file) || !@unlink($file)) {
				trigger_error("Video could not be removed: " . $file, E_USER_NOTICE);
			}
		}
	}

	public function getLastVideos($limit = 10)
	{
		$rows = $this->findAll()->order('created DESC')->limit($limit);
		return $this->createResultSet($rows);
	}

	public function getRelated($video_id, $items = 8)
	{
		$rows = $this->findAll()
			->where('id != ?', $video_id)
			->order('RAND()')
			->limit($items);

		return $this->createResultSet($rows);
	}

	public function search($query)
	{
		$result = $this->getTable('videoSearch')
			->where("MATCH(title, description, tags) AGAINST (? IN BOOLEAN MODE)", $query)
			->order("5 * MATCH(title) AGAINST (?) + MATCH(tags) AGAINST (?) + 2 * MATCH(description) AGAINST (?) DESC", $query, $query, $query);

		$ids = array();
		foreach($result as $row) {
			$ids[] = $row->id;
		}

		return (object) array(
			'videos' => $this->createResultSet($this->findAll()->where('id', $ids)),
			'total' => $result->count()
		);
	}

	public function getUserVideos($user_id)
	{
		$rows = $this->findAll()
			->where('user_id', $user_id);

		return $this->createResultSet($rows);
	}

	public function getRatedVideos($user_id, $limit = 30)
	{
		$rows = $this->findAll()
			->where(':video_ratings.user_id', $user_id)
			->order(':video_ratings.created DESC');

		return $this->createResultSet($rows);
	}

	protected function createResultSet($rows)
	{
		$result = array();
		foreach($rows as $row) {
			$result[] = Entity\Video::create($row);
		}
		return $result;
	}
}