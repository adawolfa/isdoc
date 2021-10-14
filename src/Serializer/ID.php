<?php

declare(strict_types=1);
namespace Adawolfa\ISDOC\Serializer;

/**
 * Referenced element.
 */
final class ID
{

	private object $instance;
	private ?int   $id = null;

	public function __construct(object $instance)
	{
		$this->instance = $instance;
	}

	public function getInstance(): object
	{
		return $this->instance;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

}