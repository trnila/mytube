<?php
namespace Component\Playlists;

interface IFactory {
	/**
	 * @return Playlists
	 */
    function create();
}