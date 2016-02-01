<?php
namespace Craft;

class Wistia_VideosModel extends BaseModel
{
	private $_cache_table = 'wistia_cache';

	/**
	 * Set cached API data
	 *
	 * @param  string Wistia ID
	 * @param  int    Data type
	 * @param  int    Data
	 * @return array
	 */
	public function cacheData($wistiaId, $hashedId, $type, $data)
	{
		return craft()->db->createCommand()
			->insert($this->_cache_table, [
				'wistiaId' => $wistiaId,
				'hashedId' => $hashedId,
				'type' => $type,
				'data' => serialize($data),
			]);
	}

	/**
	 * Get cached API data
	 *
	 * @param  string Wistia ID
	 * @param  int    Data type
	 * @return array
	 */
	public function getCachedData($wistiaId, $type)
	{
		return craft()->db->createCommand()
			->select('*')
			->where('wistiaId', $wistiaId)
			->where('type', $type)
			->from($this->_cache_table)
			->get()
			->row_array();
	}
}