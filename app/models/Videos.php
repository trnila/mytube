<?php
namespace Model;
use Nette\Utils\Validators;
use Nette;
use InvalidArgumentException;

class Videos extends Repository
{
	/** @var string location of incoming videos waiting for process */
	public $incomingDir;

	/** @var string location of videos directory */
	public $videosDir;

	/** @var string location of thumbnails directory */
	public $thumbnailsDir;

	/** @var string table name */
	protected $tableName = 'videos';


	/**
	 * Finds video by id
	 * @param $id string
	 * @return Model\Entity\Video
	 */
	public function find($id)
	{
		$row = parent::find($id);
		return Entity\Video::create($row);
	}

	public function addVideoToProcess(array $input, Nette\Http\FileUpload $file)
	{
		$data = $tags = array();
		foreach($input as $key => $value) {
			if(in_array($key, array('title', 'description', 'created', 'user_id'))) {
				$data[$key] = $value;
			} elseif($key == 'tags') {
				$tags = $input[$key];
			} else {
				throw new InvalidArgumentException("Input key '{$key}' is invalid!");
			}
		}

		$incomingFilePath = NULL;
		for($tries = 3; $tries > 0; $tries--) {
			try {

				// try to generate id
				$data['id'] = Nette\Utils\Strings::random(8, 'a-z0-9A-Z');

				// video with same ID already exists, skipping
				if($this->find($data['id'])) {
					continue;
				}

				$video = $this->create($data);

				foreach($tags as $tag) {
					$video->related('video_tags')
						->insert($tag);
				}

				// save file to incoming location for further process
				$incomingFilePath = $this->incomingDir . "/{$video['id']}";
				$file->move($incomingFilePath);

				// send video to queue
				$client = new \GearmanClient;
				$client->addServer();
				$client->doBackground("processVideo", $video['id']);

				return $video;
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

	public function create(array $data)
	{
		// work arround, because Nette\Database wont refetch the inserted value, because there is no return of last_insert_id
		$this->insert($data);
		return $this->find($data['id']);
	}

	public function update($id, $data)
	{
		$video = parent::find($id);
		$video->update($data);
	}

	public function deleteVideo(\ActiveRow\Video $video)
	{
		$thumbnails = [];
		foreach($video->related('thumbnails') as $thumbnail) {
			$thumbnails[] = $thumbnail->path;
		}

		$video->delete();

		// Remove a video
		if(!file_exists($file = $this->videosDir . '/' . $video->path) || !unlink($file)) {
			trigger_error("Video could not be removed: " . $file, E_USER_NOTICE);
		}

		// Remove thumbnails
		foreach($thumbnails as $thumbnail) {
			if(file_exists($file = $this->thumbnailsDir . '/' . $thumbnail) || unlink($file)) {
				trigger_error("Thumbnail could not be removed: " . $file, E_USER_NOTICE);
			}
		}
	}

	public function getLastVideos($limit = 10)
	{
		return $this->findAll()->order('created DESC');
	}
}