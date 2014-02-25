<?php
namespace Component\Comments;

interface IFactory {
	/**
	 * @return Comments
	 */
	function create();
}