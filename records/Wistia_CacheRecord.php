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
		return [
			'id' => [
				AttributeType::Number,
				'column' => ColumnType::PK
			],
			'hashedId' => AttributeType::String,
			'type' => [
				AttributeType::Enum,
				'values' => 'data, stats'
			],
			'data' => [
				AttributeType::String,
				'column' => ColumnType::Text
			]
		];
	}

	public function defineIndexes()
	{
		return [
			[
				'columns' => 'hashedId'
			],
			[
				'columns' => 'type'
			]
		];
	}
}