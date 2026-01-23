<?php
class Title
{

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getPoster(): string
    {
        return $this->poster;
    }

    public function setPoster(string $poster): void
    {
        $this->poster = $poster;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function getPlatforms(): array
    {
        return $this->platforms;
    }

    public function setPlatforms(array $platforms): void
    {
        $this->platforms = $platforms;
    }

    // --- KM3: oceny (łapki) ---
    public function getRatingUp(): int
    {
        return $this->ratingUp;
    }

    public function getRatingDown(): int
    {
        return $this->ratingDown;
    }

    public function getRatingTotal(): int
    {
        return $this->ratingUp + $this->ratingDown;
    }

    public function getRatingUpPct(): int
    {
        $t = $this->getRatingTotal();
        return $t > 0 ? (int)round(($this->ratingUp / $t) * 100) : 0;
    }

    public function getRatingDownPct(): int
    {
        $t = $this->getRatingTotal();
        return $t > 0 ? 100 - $this->getRatingUpPct() : 0;
    }

    public function __construct(
        private int $id,
        private string $name,
        private string $type,
        private int $year,
        private string $description,
        private string $poster,
        private array $categories = [],
        private array $platforms = [],
        private int $ratingUp = 0,
        private int $ratingDown = 0
    ) {}
}
