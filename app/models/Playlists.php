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

}