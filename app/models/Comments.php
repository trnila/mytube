<?php
namespace Model;
use Nette;

class Comments extends Repository
{
	/** @var string table name */
	protected $tableName = 'video_comments';

	/**
	 * Find all comments for video
	 * @param $video_id int
	 * @param $page int
	 * @param $itemsPerPage int
	 * @return array of comments
	 */
	public function findAllOrderedByDate($video_id, $page = NULL, $itemsPerPage = 10)
	{
		$query = $this->findAll()->where('video_id', $video_id)->order('created DESC');

		if($page !== NULL) {
			$query->page($page, $itemsPerPage);
		}

		$comments = array();
		foreach($query as $row) {
			$comments[] = Entity\Comment::create($row);
		}

		return $comments;
	}

	/**
	 * Add comment for video
	 * @param $video_id int
	 * @param $comment Model\Entity\Comment
	*/
	public function addComment($video_id, Entity\Comment $comment)
	{
		$this->create(array(
			'video_id' => $video_id,
			'user_id' => $comment->user_id,
			'text' => $comment->text,
			'created' => $comment->created
		));
	}

	public function find($id)
	{
		$comment = parent::find($id);
		return $comment ? Entity\Comment::create($comment) : NULL;
	}

	public function delete($comment_id)
	{
		$this->getTable()
			->wherePrimary($comment_id)
			->delete();
	}
}
