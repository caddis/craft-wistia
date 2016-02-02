<?php
namespace Craft;

class Wistia_VideosModel extends BaseModel
{
	private $_table = 'wistia_video';
	private $_cache_table = 'wistia_cache';

	/**
	 * Inserts new video record
	 *
	 * @param  string Wistia ID
	 * @param  array field
	 * @param  array element
	 * @return void
	 */
	public function insertVideo($wistiaId, $field, $element)
	{
		$db = craft()->db;

		$db->createCommand()
			->insert($this->_table, [
				'siteId' => 1,
				'sectionId' => $element->sectionId,
				'entryId' => $element->id,
				'fieldId' => $field->id,
				'type' => $element->getElementType(),
				'wistiaId' => $wistiaId,
				'memberId' => craft()->userSession->getUser()->id,
				'position' => 1
			]);

		return $db->getLastInsertId();
	}

	/**
	 * Updates video record
	 *
	 * @param  array ID
	 * @param  int   Position
	 * @return void
	 */
	public function updateVideo($videoId)
	{
		craft()->db->createCommand()
			->update($this->_table, '', [
				'id=' . $videoId
			]);
	}

	/**
	 * Removes a video record
	 *
	 * @param  int  ID
	 * @return void
	 */
	public function removeVideo($videoId)
	{
		craft()->db->createCommand()
			->delete($this->_table, [
				'id' => $videoId
			]);
	}

	/**
	 * Get stored videos
	 *
	 * @param  string Entry ID
	 * @param  string Field ID
	 * @param  string Order
	 * @param  string Sort
	 * @param  int    Limit
	 * @param  int    Offset
	 *
	 * @return array  Video records
	 */
	public function getStoredVideos($entryId, $fieldId = false, $order = 'position', $sort = 'asc', $limit = 10000, $offset = 0)
	{
		$results = craft()->db->createCommand()->select('*');

		if ($entryId) {
			$results = $results->where('entryId=' . $entryId);
		}

		if ($fieldId !== false) {
			$results = $results->andWhere('fieldId=' . $fieldId);
		}

		$results = $results->from($this->_table)
			->order($order, $sort)
			->limit($limit, $offset)
			->group('wistiaId')
			->queryAll();

		return $results;
	}

	/**
	 * Get single video
	 *
	 * @param  string Wistia ID
	 * @return array  Record
	 */
	public function getVideoByWistiaId($wistiaId, $entryId = false, $fieldId = false)
	{
		$results = craft()->db->createCommand()->select('*')
			->where('wistiaId=' . $wistiaId);

		if ($entryId !== false) {
			$results = $results->where('entryId=' . $entryId);
		}

		if ($fieldId !== false) {
			$results = $results->where('fieldId=' . $fieldId);
		}

		$results = $results->from($this->_table)->queryRow();

		return $results;
	}

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
		craft()->db->createCommand()
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
		craft()->db->createCommand()
			->select('*')
			->where('wistiaId', $wistiaId)
			->where('type', $type)
			->from($this->_cache_table)
			->get()
			->row_array();
	}
}