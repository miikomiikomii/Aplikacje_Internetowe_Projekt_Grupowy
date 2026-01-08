<?php
class Title
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public int $year,
        public string $description,
        public string $poster,
        public array $categories = [],
        public array $platforms = []
    ) {}
}
