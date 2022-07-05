<?php

namespace Controller\API\Draft;

use Controller\API\APIResponse;
use Model\Article;
use Model\ArticleChange;
use Util\Singleton\Session;

class UpdateDraft extends APIResponse
{
    protected function get_response_object(): object|array
    {
        if (Session::instance()->isLoggedIn() && isset($_REQUEST['draft_id'])) {
            $article_change = ArticleChange::getById((int)$_REQUEST['draft_id']);
            if ($article_change === null) {
                return [];
            }

            $new_article_change = $article_change->updateFields(
                null,
                $_REQUEST['title'] ?? null,
                $_REQUEST['contents'] ?? null,
                is_numeric($_REQUEST['category_id']) ? (int) $_REQUEST['category_id'] : null,
                is_numeric($_REQUEST['ready']) ? (bool) $_REQUEST['ready'] : null,
            );

            if ($new_article_change->article->status === Article::STATUS_DRAFT) {
                $new_article_change->article->applyChange($new_article_change);
            }

            return [
                'draft_id' => $new_article_change->id
            ];
        }
        return [];
    }
}