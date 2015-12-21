<?php
namespace Craft;

class Wistia_CacheRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'wistia_cache';
	}

	protected function defineAttributes()
	{
		return array(
			'id' => array(
				AttributeType::Number,
				'column' => ColumnType::PK
			),
			'hashedId' => AttributeType::String,
			'type' => array(
				AttributeType::Enum,
				'values' => 'data, stats'
			),
			'data' => array(
				AttributeType::String,
				'column' => ColumnType::Text
			)
		);
	}

	public function defineIndexes()
	{
		return array(
			array(
				'columns' => 'hashedId'
			),
			array(
				'columns' => 'type'
			)
		);
	}
}