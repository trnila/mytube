<?php
namespace Worker\Job;
use FFMpeg;
use Nette;
use Exception;

class ProcessVideo extends Job
{
	/**
	 * @var FFMpeg\FFMpeg
	 * @inject
	*/
	public $ffmpeg;

	/**
	 * @var FFMpeg\FFProbe
	 * @inject
	*/
	public $ffprobe;

	/**
	 * @var Model\Videos
	 * @inject
	*/
	public $videos;

	/**
	 * @var int
	*/
	public $thumbnailsNum = 3;

	protected function createthumbnails($video)
	{
		$thumbnails = array();

		// if its video - generate thumbnails
		if($video->isVideo) {
			$ffmpeg = $this->ffmpeg->open($video->filePath);

			$duration = $video->duration;
			$duration = floor($duration - $duration * 1 / 20);
			$stepTime = floor($duration / $this->thumbnailsNum);
			for($shot = 1; $shot <= $this->thumbnailsNum; $shot++) {
				$time = $stepTime * $shot;

				$temporaryName = $this->generateTemporaryFileName();

				$ffmpeg->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($time))
					->save($temporaryName, TRUE);

				if($this->prepareThumbnail($temporaryName, __DIR__ . '/../../www/thumbnails/' . $video->id . "-" . $shot . ".png")) {
					$thumbnails[] = array(
						'num' => $shot,
						'time' => $time
					);
				}
			}
		} else { // if not try to find image in audio
			$temporaryName = $this->generateTemporaryFileName('.png');

			`ffmpeg -v quiet -y -i {$video->filePath} -an -vcodec png {$temporaryName}`;

			if($this->prepareThumbnail($temporaryName, __DIR__ . '/../../www/thumbnails/' . $video->id . '-1.png')) {
				$thumbnails[] = array(
					'num' => 1,
					'time' => NULL
				);
			}
		}

		return $thumbnails;
	}

	protected function convertToWebm($video)
	{
		$ffmpeg = $this->ffmpeg->open($video->filePath);

		$format = new FFMpeg\Format\Video\WebM();
		$format->on('progress', function ($video, $format, $percentage) {
			echo "$percentage % transcoded";
		});

		$ffmpeg->save($format, __DIR__ . "/../../www/videos/{$video->id}.webm");
	}

	public function execute(\GearmanJob $job)
	{
		$video = new Video;
		$video->id = $job->workload();
		$this->logger->info("Procesing {$video->id}");

		$row = $this->videos->find($video->id);
		if(!$row) {
			throw new Exception("Video not found!");
		}

		$video->filePath = __DIR__ . '/../../incoming/' . $video->id;

		//$video->filePath = '/home/daniel/Videos/a.mp4';
		//$file = '/home/daniel/Downloads/LittleLight - Ring The Bells (Christmas Special).mp3';

		// get meta data from video
		$this->extractMetadata($video);

		$this->logger->info('Video: ' . ($video->isVideo ? 'true' : 'false'));
		$this->logger->info("Duration: {$video->duration} seconds");

		// generate thumbnails
		$video->thumbnails = $this->createthumbnails($video);

		// convert to webm format
		$this->convertToWebm($video);

		$this->logger->info('Adding video to database');

		$this->videos->update($video->id, array(
			'duration' => $video->duration,
			'isvideo' => $video->isVideo,
			'jobid' => NULL
		));

		// add a screnshots
		foreach($video->thumbnails as $thumbnail) {
			$this->videos->addThumbnail($row, $thumbnail['num'], $thumbnail['time']);
		}

		$this->logger->info('Video successfully converted');
	}

	protected function getTempDirectory()
	{
		return 'tmp/';
	}

	protected function generateTemporaryFileName($extension = '')
	{
		return $this->getTempDirectory() . '/' . Nette\Utils\Strings::random() . ($extension ? '.' . $extension : '');
	}

	protected function extractMetadata($video)
	{
		$meta = $this->ffprobe->streams($video->filePath);

		$video->isVideo = FALSE;
		// we need to find at least one stream that is not a png image
		foreach($meta->videos() as $stream) {
			if($stream->has('codec_name') && !in_array($stream->get('codec_name'), array('png', 'mjpeg'))) {
				$video->isVideo = TRUE;
				break;
			}
		}

		$video->duration = $this->getDuration($video);
	}

	/**
	 * calculates duration of media, if media supports meta than grab it, otherwise calculate it direct via ffmpeg
	 *
	 * @return float
	 */
	protected function getDuration($video)
	{
		$streams = $this->ffprobe->streams($video->filePath);
		try {
			if($video->isVideo) {
				return $streams->videos()->first()->get('duration');
			} else {
				return $streams->audios()->first()->get('duration');
			}
		} catch(FFMpeg\Exception\InvalidArgumentException $e) {
			ob_start();
			echo `ffmpeg -i "{$video->filePath}" 2>&1`;
			$output = ob_get_clean();

			preg_match("#Duration: (\d\d):(\d\d):(\d\d(\.\d\d)?)#", $output, $result);
			$duration = ((int) $result[1]) * 60 * 60; // hours
			$duration += ((int) $result[2]) * 60; // minutes
			$duration += ((float) $result[3]);
			return $duration;
		}
	}

	protected function prepareThumbnail($temporaryName, $destination)
	{
		try {
			if(!file_exists($temporaryName)) {
				throw new Exception("Thumbnail '$temporaryName' does not exists!");
			}

			Nette\Image::fromFile($temporaryName)
				->resize(185, 104)
				->save($destination);

			@unlink($temporaryName);

			return TRUE;
		} catch(Exception $e) {
			@unlink($temporaryName);
			$this->logger->error($e->getMessage());
		}

		return FALSE;
	}
}

class Video extends Nette\Object {
	public $id;
	public $duration;
	public $thumbnails;
	public $isVideo;
	public $filePath;
}