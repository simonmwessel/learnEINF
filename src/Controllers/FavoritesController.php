<?php
session_start();

class FavoritesController {
    public function index() {
        $favorites = isset($_SESSION['favorites']) ? $_SESSION['favorites'] : [];

        $questions = getQuestionsByIds($favorites);

        include 'views/favorites.php';
    }

    public function addFavorite($id) {
        if (!isset($_SESSION['favorites'])) {
            $_SESSION['favorites'] = [];
        }
        if (!in_array($id, $_SESSION['favorites'])) {
            $_SESSION['favorites'][] = $id;
        }
        echo json_encode(['success' => true]);
    }

    public function removeFavorite($id) {
        if (isset($_SESSION['favorites'])) {
            $_SESSION['favorites'] = array_filter($_SESSION['favorites'], function($favId) use ($id) {
                return $favId != $id;
            });
        }
        echo json_encode(['success' => true]);
    }
}
