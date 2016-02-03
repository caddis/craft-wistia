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
		craft()->db
			->createCommand()
			->delete($this->_table, 'id=' . $videoId);
	}

	/**
	 * Clear video field
	 *
	 * @param  int Entry ID
	 * @return void
	 */
	public function clearVideos($entryId)
	{
		craft()->db
			->createCommand()
			->delete($this->_table, 'entryId=' . $entryId);
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
		$command = craft()->db->createCommand();

		$command->select('*');

		if ($entryId) {
			$command->where('entryId=' . $entryId);
		}

		if ($fieldId !== false) {
			$command->andWhere('fieldId=' . $fieldId);
		}

		$results = $command->from($this->_table)
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
		$command = craft()->db->createCommand();

		$command->select('*')
			->where('wistiaId=' . $wistiaId);

		if ($entryId !== false) {
			$command->andWhere('entryId=' . $entryId);
		}

		if ($fieldId !== false) {
			$command->andWhere('fieldId=' . $fieldId);
		}

		$results = $command->from($this->_table)->queryRow();

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
		craft()->db
			->createCommand()
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
		craft()->db
			->createCommand()
			->select('*')
			->where('wistiaId', $wistiaId)
			->where('type', $type)
			->from($this->_cache_table)
			->get()
			->row_array();
	}
}