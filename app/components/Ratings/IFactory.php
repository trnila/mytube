<?php
namespace Component\Ratings;

interface IFactory {
	/**
	 * @return Ratings
	 */
	function create();
}