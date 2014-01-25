<?php
namespace Worker\Job;
use FFMpeg;

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

	protected function createthumbnails($filePath, $video)
	{
		$thumbnails = array();

		// if its video - generate thumbnails
		if($video->isVideo) {
			$ffmpeg = $this->ffmpeg->open($filePath);

			$duration = $video->duration;
			$duration = floor($duration - $duration * 1 / 20);
			$stepTime = floor($duration / $this->thumbnailsNum);
			for($shot = 1; $shot <= $this->thumbnailsNum; $shot++) {
				$time = $stepTime * $shot;

				$ffmpeg->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($time))
					->save(__DIR__ . '/../../www/thumbnails/' . $video->id . "-" . $shot . ".png", TRUE);

				$thumbnails[] = array(
					'num' => $shot,
					'time' => $time
				);
		}
		} else { // if not try to find image in audio
			$thumb = $this->getTempDirectory() . $video->id . '.png';

			`ffmpeg -v quiet -y -i $file -an -vcodec copy $thumb`;
			if(file_exists($thumb)) {
				$thumbnails[] = $thumb;
			}
		}
		return $thumbnails;
	}

	protected function convertToWebm($filePath, $video)
	{
		$ffmpeg = $this->ffmpeg->open($filePath);


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

		$filePath = __DIR__ . '/../../incoming/' . $video->id;

		//$filePath = '/home/daniel/Videos/a.mp4';
		//$file = '/home/daniel/Downloads/LittleLight - Ring The Bells (Christmas Special).mp3';

		// get meta data from video
		$meta = $this->ffprobe->streams($filePath);
		$video->duration = $this->getDuration($filePath);
		$video->isVideo = (bool) count($meta->videos());

		$this->logger->info('Video: ' . ($video->isVideo ? 'true' : 'false'));
		$this->logger->info("Duration: {$video->duration} seconds");

		// generate thumbnails
		$video->thumbnails = $this->createthumbnails($filePath, $video);
		$this->convertToWebm($filePath, $video);

		$this->logger->info('Adding video to database');

		$row->update(array(
			'duration' => $video->duration,
			'isvideo' => $video->isVideo,
			'jobid' => NULL
		));

		// add a screnshots
		foreach($video->thumbnails as $thumbnail) {
			$row->related('video_thumbnails')
				->insert(array(
					'number' => $thumbnail['num'],
					'time' => $thumbnail['time']
				));
		}

		$this->logger->info('Video successfully converted');
	}

	protected function getTempDirectory()
	{
		return 'tmp/';
	}

	/**
	 * calculates duration of media, if media supports meta than grab it, otherwise calculate it direct via ffmpeg
	 *
	 * @return float
	 */
	protected function getDuration($filePath)
	{
		$streams = $this->ffprobe->streams($filePath);
		try {
			return $streams->videos()->first()->get('duration');
		} catch(FFMpeg\Exception\InvalidArgumentException $e) {
			ob_start();
			echo `ffmpeg -i "{$filePath}" 2>&1`;
			$output = ob_get_clean();

			preg_match("#Duration: (\d\d):(\d\d):(\d\d(\.\d\d)?)#", $output, $result);
			$duration = ((int) $result[1]) * 60 * 60; // hours
			$duration += ((int) $result[2]) * 60; // minutes
			$duration += ((float) $result[3]);
			return $duration;
		}
	}
}

class Video {
	public $id;
	public $duration;
	public $thumbnails;
	public $isVideo;
}