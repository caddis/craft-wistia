<?php
namespace Craft;

class Wistia_VideosRecord extends BaseRecord
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
			'siteId' => [
				AttributeType::Bool,
				'default' => 1
			],
			'sectionId' => [
				AttributeType::Number,
				'column' => ColumnType::Int
			],
			'entryId' => [
				AttributeType::Number,
				'column' => ColumnType::Int
			],
			'fieldId' => [
				AttributeType::Number,
				'column' => ColumnType::Int
			],
			'type' => [
				AttributeType::Enum,
				'values' => 'single, channel, structure'
			],
			'wistiaId' => AttributeType::Number,
			'hashedId' => AttributeType::Number,
			'memberId' => AttributeType::Number,
			'position' => [
				AttributeType::Bool,
				'default' => 1
			]
		];
	}

	public function defineIndexes()
	{
		return [
			[
				'columns' => [
					'type',
					'wistiaId',
					'hashedId'
				]
			]
		];
	}
}