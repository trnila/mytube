<?php
namespace Model;
use Nette\Utils\Validators;
use Nette;

class Videos extends Repository
{
	/** @var string location of incoming videos waiting for process */
	public $incomingDir;

	/** @var string location of videos directory */
	public $videosDir;

	/** @var string location of thumbnails directory */
	public $thumbnailsDir;

	/** @var string table name */
	protected $name = 'videos';

	public function addVideoToProcess(array $data, Nette\Http\FileUpload $file)
	{
		try {
			$video = FALSE;
			$tries = 15;

			$this->manager->connection->beginTransaction();

			for($x = 0; $x < $tries; $x++) {
				$data['id'] = Nette\Utils\Strings::random(8, 'a-z0-9A-Z');
				try {
					$video = $this->create($data);
					break;
				}
				catch(\Database\DuplicateEntryException $e) {
				}
			}

			if(!$video) {
				throw new \RuntimeException('Could not generate unique ID for new video.');
			}

			// save file to incoming location for further process
			@$file->move($this->incomingDir . "/{$video['id']}");

			// send video to queue
			$ch = @Nette\Environment::getContext()->workqueue__proccessVideo; //TODO: this is not clean
			$msg_body = "{$video['id']}";
			$msg = new \PhpAmqpLib\Message\AMQPMessage($msg_body, array('content_type' => 'text/plain'));
			$ch->basic_publish($msg, "", "proccessVideo");

		}
		catch(\Exception $e) {
			if($video) {
				@unlink($this->incomingDir . "/{$video['id']}");
			}
		}

		$this->manager->connection->commit();

		return $video;
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
}