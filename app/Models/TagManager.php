<?php


namespace App\Models;


use Nette\Database\Explorer;

class TagManager
{
    const TABLE_NAME = 'tags';

    /** @var Explorer $database */
    private $database;

    /**
     * TagManager constructor.
     * @param Explorer $database
     */
    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function add(string $title): bool
    {
        $slug = \URLify::slug($title);
        $this->database->table(self::TABLE_NAME)->insert([
            'slug' => $slug,
            'title' => $title
        ]);
        return true;
    }

    public function getAllCount()
    {
        return $this->database->table(self::TABLE_NAME)->count();
    }

    public function findAll(int $limit, int $offset)
    {
        return $this->database->table(self::TABLE_NAME)->limit($limit, $offset);
    }

    public function findAllForSelect()
    {
        return $this->database->table(self::TABLE_NAME)->fetchPairs('slug', 'title');
    }

    public function get($slug)
    {
        return $this->database->table(self::TABLE_NAME)->get($slug);
    }

    public function edit($slug, $title): bool
    {
        $new_slug = \URLify::slug($title);
        return $this->get($slug)->update([
            'slug' => $new_slug,
            'title' => $title
        ]);
    }

    public function remove($slug): bool
    {
        return (bool)$this->get($slug)->delete();
    }

}