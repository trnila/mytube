<?php
namespace Component\Videos;

interface IFactory {
	/**
	 * @return Videos
	 */
	function create();
}