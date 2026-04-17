<?php
namespace Infomodus\Fedexlabel\Model\Packer;

use DVDoug\BoxPacker\Item;

class TestItem implements Item
{

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var int
     */
    private $weight;

    /**
     * @var int
     */
    private $keepFlat;

    /**
     * @var int
     */
    private $volume;

    /**
     * @var array
     */
    private $productOptions;

    /**
     * TestItem constructor.
     *
     * @param string $description
     * @param int $width
     * @param int $length
     * @param int $depth
     * @param int $weight
     * @param int $keepFlat
     * @param array $options
     */
    public function __construct($description, $width, $length, $depth, $weight, $keepFlat, $options=[])
    {
        $this->description = $description;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
        $this->weight = $weight;
        $this->keepFlat = $keepFlat;
        $this->productOptions = $options;

        $this->volume = $this->width * $this->length * $this->depth;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->description;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @return int
     */
    public function getVolume(): int
    {
        return $this->volume;
    }

    /**
     * @return int
     */
    public function getKeepFlat(): bool
    {
        return $this->keepFlat;
    }

    /**
     * @return int
     */
    public function getProductOptions()
    {
        return $this->productOptions;
    }
}

