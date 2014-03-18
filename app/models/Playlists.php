<?php
namespace Model;
use Nette;
use DateTime;

class Playlists extends Repository
{
	/** @var string table name */
	protected $tableName = 'playlists';

	public function find($id)
	{
		$row = parent::find($id);
		return $row ? Entity\Playlist::create($row) : NULL;
	}

	public function addVideo($playlist_id, $video_id)
	{
		//TODO: fix unique constraint checking

		$row = parent::find($playlist_id);
		$row->related('playlist_videos')
			->insert(array(
				'video_id' => $video_id,
				'added' => new DateTime
			));
	}

	public function setPrivate($playlist_id, $private = TRUE)
	{
		$this->getTable()
			->wherePrimary($playlist_id)
			->update(array(
				'private' => $private
			));
	}

	public function removeVideo($playlist_id, $video_id)
	{
		$this->getTable('playlist_videos')
			->where('video_id', $video_id)
			->where('playlist_id', $playlist_id)
			->delete();
	}

	public function delete($playlist_id)
	{
		$this->getTable('playlists')
			->wherePrimary($playlist_id)
			->delete();
	}

	public function getNextVideo(Entity\Playlist $playlist)
	{
		$result = $this->getTable('playlist_videos')
			->where('playlist_id', $playlist->id)
			->order('added')
			->fetch();

		return $result ? Entity\Video::create($result->video) : NULL;
	}

	public function getAll($user_id, $video_id)
	{
		$rows = $this->getTable()
			->where('user_id', $user_id);

		$playlists = array();

		foreach($rows as $row) {
			$playlists[] = (object) array(
				'contained' => (bool) $row->related('playlist_videos')->where('video_id', $video_id)->fetch(),
				'playlist' => Entity\Playlist::create($row)
			);
		}

		return $playlists;
	}

	public function getVideos($playlist)
	{
		$rows = $this->getTable('playlist_videos')
			->where('playlist_id', $playlist->id);

		$videos = array();
		foreach($rows as $row) {
			$videos[] = Entity\Video::create($row->video);
		}
		return $videos;
	}

	public function getPublic($user_id)
	{
		$rows = $this->findAll()
			->select('playlists.*, COUNT(:playlist_videos.video_id) AS total')
			->where('user_id', $user_id)
			->where('private', false)
			->group('playlists.id')
			->order('COUNT(:playlist_videos.video_id)');

		$result = array();
		foreach($rows as $row) {
			$result[] = (object) array(
				'total' => (int) $row->total,
				'playlist' => Entity\Playlist::create($row)
			);
		}

		return $result;
	}

}