<?php
namespace Craft;

class Wistia_Videos extends BaseRecord
{
	public function getTableName()
	{
		return 'wistia_video';
	}

	protected function defineAttributes()
	{
		return [
			'id' => [
				AttributeType::Number,
				'column' => ColumnType::PK
			],
			'siteId' => AttributeType::Bool,
			'sectionId' => AttributeType::Number,
			'entryId' => AttributeType::Number,
			'fieldId' => AttributeType::Number,
			'type' => [
				AttributeType::Enum,
				'values' => 'single, channel, structure'
			],
			'wistiaId' => AttributeType::Number,
			'hashedId' => AttributeType::Number,
			'memberId' => AttributeType::Number,
			'position' => AttributeType::Bool
		];
	}

	public function defineIndexes()
	{
		return [
			[
				'columns' => 'type'
			],
			[
				'columns' => 'wistiaId'
			],
			[
				'columns' => 'hashedId'
			]
		];
	}
}