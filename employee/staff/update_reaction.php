<?php
session_start();

// Default reactions count
$likes = isset($_SESSION['likes']) ? $_SESSION['likes'] : 7100;
$reaction = isset($_SESSION['reaction']) ? $_SESSION['reaction'] : "👍 Like";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userReaction = $_POST["reaction"];

    // Emoji labels
    $reactionText = [
        "like" => ["👍", "Like"],
        "love" => ["❤️", "Love"],
        "haha" => ["😂", "Haha"],
        "wow" => ["😮", "Wow"],
        "sad" => ["😢", "Sad"],
        "angry" => ["😡", "Angry"]
    ];

    // Update reaction session
    if (isset($reactionText[$userReaction])) {
        $reaction = $reactionText[$userReaction][0] . " " . $reactionText[$userReaction][1];
        $_SESSION['reaction'] = $reaction;
        $_SESSION['likes'] = ++$likes; // Increase count on reaction
    }

    echo json_encode(["reaction" => $reactionText[$userReaction][0], "text" => $reactionText[$userReaction][1], "likes" => $likes]);
}
?>
