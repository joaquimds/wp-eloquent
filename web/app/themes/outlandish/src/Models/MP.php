<?php

namespace Outlandish\Website\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Outlandish\Wordpress\Eloquoowp\Models\Base;
use Outlandish\Wordpress\Oowp\PostTypes\WordpressPost;

class MP extends Base
{
    protected $table = 'mps';

    public static function createTable()
    {
        Capsule::schema()->create('mps', function ($table) {
            $table->integer('id')->unique();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
    }

    public function createWordpressPost()
    {
        $postId = wp_insert_post([
            'ID' => $this->id,
            'post_title' => $this->name
        ]);
        $post = WordpressPost::fetchById($postId);
        return $post;
    }

    public function updateWordpressPost(WordpressPost $post)
    {
        $post->setMetadata('email', $this->email);
    }

    public function updateFromPost(WordpressPost $post)
    {
        $this->name = $post->title();
        $this->email = $post->metadata('email');
        $this->save();
    }
}
