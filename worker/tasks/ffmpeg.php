<?php
namespace Task;

class ffmpeg extends Task
{
	protected function getMetaData($file)
	{
		$this->logger->info('Getting informations from video...');
		$output = shell_exec("ffprobe -v quiet -print_format json -show_format -show_streams $file");
		//$this->logger->debug($output);
		$meta = json_decode($output);
		return $meta;
	}

	protected function createScreenshots($file, $video)
	{
		$screenshots = array();

		// if its video - generate screenshots
		if($video->isVideo) {
			for($i = 1; $i <= 5; $i++) {
				$time = $i / 5 * $video->duration;
				$time -= 0.25 * $time;

				$thumb = $this->getTempDirectory() . $video->id . "_{$i}.jpg";

				$this->logger->info("Generating screenshot at time {$time}s");
				`ffmpeg -y -v quiet -ss $time -i $file -y -f image2 -vcodec mjpeg -vframes 1 $thumb`;
				if(file_exists($thumb)) {
					$screenshots[] = $thumb;
				}
			}
		} else { // if not try to find image in audio
			$thumb = $this->getTempDirectory() . $video->id . '.png';

			`ffmpeg -v quiet -y -i $file -an -vcodec copy $thumb`;
			if(file_exists($thumb)) {
				$screenshots[] = $thumb;
			}
		}
		return $screenshots;
	}

	protected function convertToWebm($file, $video)
	{
		$descriptors = array(
			0 => array('file', '/dev/null', 'r'),
			1 => array('file', '/dev/null', 'w'),
			2 => array('pipe', 'w')
		);

		$process = proc_open("ffmpeg -v quiet -y -i $file tmp/kuscus.webm", $descriptors, $pipes);

	    stream_set_blocking($pipes[2], 0);
		while(!feof($pipes[2])) {
			$line = trim(fgets($pipes[2]));
			if(!empty($line)) {
				preg_match('/time=([^ ]+)/', $line, $matches);
				if(isset($matches[1])) {
					$tmp = explode(":", $matches[1]);

					$time = $tmp[0] * 60 * 60 + $tmp[1] * 60 + $tmp[2];

					$job->sendStatus(floor($time / $video->duration * 100), 100);
				}

				//$this->logger->debug($line);
			}

		}
	}

	public function process(\GearmanJob $job)
	{
		$video = new Video;
		$video->id = $job->workload();

		$this->logger->info("Procesing {$video->id}");

		$file = '/home/daniel/Videos/a.mp4';
		//$file = '/home/daniel/Downloads/LittleLight - Ring The Bells (Christmas Special).mp3';
		$file = escapeshellarg($file);

		// get meta data from video
		$meta = $this->getMetaData($file);


		$video->duration = isset($meta->format, $meta->format->duration) ? $meta->format->duration : 0;
		$video->isVideo = $meta->streams[0]->codec_type == 'video';

		$this->logger->info('Video: ' . ($video->isVideo ? 'true' : 'false'));
		$this->logger->info("Duration: {$video->duration} seconds");

		// generate screenshots
		$video->screenshots = $this->createScreenshots($file, $video);

		$this->convertToWebm($file, $video);

		var_dump($video);

		exit;
	}

	protected function getTempDirectory()
	{
		return 'tmp/';
	}
}

class Video {
	public $id;
	public $duration;
	public $screenshots;
	public $isVideo;
}