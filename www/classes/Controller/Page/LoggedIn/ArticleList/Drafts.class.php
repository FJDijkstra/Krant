<?php

namespace Controller\Page\LoggedIn\ArticleList;

use Controller\Page\LoggedIn\ArticleList;
use Model\Article;

/**
 * Drafts pagina.
 */
class Drafts extends ArticleList
{
    public function __construct()
    {
        parent::__construct(Article::getAllDrafts(), 'Drafts');
    }

    public function allowed_roles(): array
    {
        return [3];
    }
}
