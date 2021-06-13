<?php


namespace App\Models;


use App\Models\Exceptions\ImageSizeNotAllow;
use Nette\Database\Explorer;
use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Tracy\Debugger;

class ImageManager
{
    const TABLE_NAME = 'images';
    const TABLE_NAME_TO_TAGS = 'tags2images';

    const IMAGE_SIZE_BIG = 'big',
        IMAGE_SIZE_THUMB = 'thumb';

    /** @var string $imagesDir */
    private $imagesDir;

    /**
     * @var string $imagesUrl
     */
    private $imagesUrl;

    /** @var Explorer $database */
    private $database;

    /**
     * ImageManager constructor.
     * @param string $imagesDir
     * @param string $imagesUrl
     * @param Explorer $database
     */
    public function __construct(string $imagesDir, string $imagesUrl, Explorer $database)
    {
        $this->imagesDir = $imagesDir;
        $this->imagesUrl = $imagesUrl;
        $this->database = $database;
    }

    /**
     * Размеры и настройки размеров
     * @return \array[][]
     */
    public static function getAllowedSizes(): array
    {
        return [
            self::IMAGE_SIZE_BIG => [
                [1024, null],
                [100, Image::JPEG]
            ],
            self::IMAGE_SIZE_THUMB => [
                [512, 512, Image::FILL],
                [80, Image::JPEG]
            ]
        ];
    }

    public static function isAllowSize(string $size): bool
    {
        $allowed = self::getAllowedSizes();
        return isset($allowed[$size]);
    }

    public static function fileName(string $slug, string $size)
    {
        if (!self::isAllowSize($size)) {
            throw new ImageSizeNotAllow('Image size "{$size}" not allow');
        }
        return "{$slug}_{$size}.jpg";
    }

    public function imageUrl(string $slug, string $size)
    {
        $filename = self::fileName($slug, $size);
        return "$this->imagesUrl/$filename";
    }

    public function linkTags(string $image_slug, array $tags)
    {
        $values = array_map(function ($item) use ($image_slug) {
            return ['image' => $image_slug, 'tag' => $item];
        }, $tags);
        if (count($values) >= 1) {
            $this->database->table(self::TABLE_NAME_TO_TAGS)->insert($values);
        }
    }

    public function findTags(string $image_slug): array
    {
        return $this->database->table(self::TABLE_NAME_TO_TAGS)
            ->where('image', $image_slug)
            ->fetchPairs('tag', 'tag');
    }

    public function relinkTags(string $image_slug, array $tags)
    {
        $exists = $this->findTags($image_slug);
        $toRemove = array_diff(array_values($exists), array_values($tags));
        $toAdd = array_diff(array_values($tags), array_values($exists));
        $this->database->table(self::TABLE_NAME_TO_TAGS)
            ->where('image', $image_slug)
            ->where('tag', $toRemove)
            ->delete();
        $this->linkTags($image_slug, $toAdd);
    }

    public function unlinkTags(string $image_slug)
    {
        $this->database->table(self::TABLE_NAME_TO_TAGS)
            ->where('image', $image_slug)
            ->delete();
    }

    private function uploadImage($slug, FileUpload $image)
    {
        foreach (self::getAllowedSizes() as $size => $settings) {
            $path = $this->imagesDir . DIRECTORY_SEPARATOR . self::fileName($slug, $size);
            $newImage = $image->toImage();
            $newImage->resize($settings[0][0], $settings[0][1], $settings[0][2] ?? Image::FIT);
            $newImage->save($path, $settings[1][0], $settings[1][1]);
        }
    }

    private function removeImage(string $slug): bool
    {
        foreach (self::getAllowedSizes() as $size => $settings) {
            $path = $this->imagesDir . DIRECTORY_SEPARATOR . self::fileName($slug, $size);
            if (file_exists($path)) {
                unlink($path);
            }
        }
        return true;
    }

    public function add(FileUpload $image, string $title, string $description, array $tags)
    {
        $slug = \URLify::slug($title);
        $this->database->table(self::TABLE_NAME)->insert([
            'slug' => $slug,
            'title' => $title,
            'description' => $description
        ]);
        $this->uploadImage($slug, $image);
        $this->linkTags($slug, $tags);
    }

    public function getAllCount()
    {
        return $this->database->table(self::TABLE_NAME)->count();
    }

    public function findAll(int $limit, int $offset)
    {
        return $this->database->table(self::TABLE_NAME)->limit($limit, $offset);
    }

    public function get(string $slug)
    {
        return $this->database->table(self::TABLE_NAME)->get($slug);
    }

    public function edit(string $slug, FileUpload $image, string $title, string $description, array $tags)
    {
        if ($image->isImage()) {
            $this->removeImage($slug);
            $this->uploadImage($slug, $image);
        }
        $this->get($slug)->update([
            'title' => $title,
            'description' => $description,
        ]);
        $this->relinkTags($slug, $tags);
    }

    public function remove($slug)
    {
        $this->removeImage($slug);
        $this->unlinkTags($slug);
        $this->database->table(self::TABLE_NAME)
            ->get($slug)
            ->delete();
    }

}