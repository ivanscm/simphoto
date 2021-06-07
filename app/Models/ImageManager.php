<?php


namespace App\Models;


use Nette\Database\Explorer;
use Nette\Http\FileUpload;
use Nette\Utils\Image;

class ImageManager
{
    const TABLE_NAME = 'images';
    const TABLE_NAME_TO_TAGS = 'tags2images';

    /** @var string $imagesDir */
    private $imagesDir;

    /** @var Explorer $database */
    private $database;

    /**
     * ImageManager constructor.
     * @param string $imagesDir
     * @param Explorer $database
     */
    public function __construct(string $imagesDir, Explorer $database)
    {
        $this->imagesDir = $imagesDir;
        $this->database = $database;
    }

    public function addTags(string $image_slug, array $tags)
    {
        $values = array_map(function ($item) use ($image_slug) {
            return ['image' => $image_slug, 'tag' => $item];
        }, $tags);
        if (count($values) >= 1) {
            $this->database->table(self::TABLE_NAME_TO_TAGS)->insert($values);
        }
    }

    public function add(FileUpload $image, string $title, string $description, array $tags)
    {
        $slug = \URLify::slug($title);
        $this->database->table(self::TABLE_NAME)->insert([
            'slug' => $slug,
            'title' => $title,
            'description' => $description
        ]);
        $fullImagePath = $this->imagesDir . DIRECTORY_SEPARATOR . "{$slug}_big.jpg";
        $fullImage = $image->toImage();
        $fullImage->resize(null, 768);
        $fullImage->save($fullImagePath, 100, Image::JPEG);
        $this->addTags($slug, $tags);
    }


}