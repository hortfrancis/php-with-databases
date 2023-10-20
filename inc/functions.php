<?php

function full_catalog_array()
{
    include('connection.php');

    try {
        // Returning a PDOStatement object
        $results = $conn->query("SELECT media_id, title, category, img FROM Media");

    } catch (Exception $e) {
        echo 'Could not retrieve data: ' . $e->getMessage();;
        exit;
    }

    $catalog = $results->fetchAll();
    return $catalog;
}

function random_catalog_array()
{
    include('connection.php');

    try {
        // Returning a PDOStatement object
        $results = $conn->query("
        SELECT media_id, title, category, img 
        FROM Media
        ORDER BY RAND()
        LIMIT 4
        ;");
        
    } catch (Exception $e) {
        echo 'Could not retrieve data: ' . $e->getMessage();;
        exit;
    }

    $catalog = $results->fetchAll();
    return $catalog;
}

function single_item_array($id)
{
    include('connection.php');

    try {
        // Prepare a SQL statement to prevent malicious query injection
        $results = $conn->prepare("
            SELECT Media.media_id, title, category, img, format, year, genre, publisher, isbn
            FROM Media
            JOIN Genres ON Media.genre_id = Genres.genre_id
            LEFT OUTER JOIN Books ON Media.media_id = Books.media_id
            WHERE Media.media_id = ?
        ;");
        // Sanitise and add the media item item
        $results->bindParam(1, $id, PDO::PARAM_INT);
        // Returning a PDOStatement object
        $results->execute();
    } catch (Exception $e) {
        echo 'Could not retrieve data: ' . $e->getMessage();;
        exit;
    }

    $item = $results->fetch();

    // Return `false` if the media item is not found
    if (empty($item)) return $item;

    // Add people associated with the media item as a nested associative array
    try {
        $results = $conn->prepare("
            SELECT fullname, role
            FROM Media_People
            JOIN People ON Media_People.people_id = People.people_id
            WHERE Media_People.media_id = ?
        ;");
        $results->bindParam(1, $id, PDO::PARAM_INT);
        $results->execute();
    } catch (Exception $e) {
        echo 'Could not retrieve data: ' . $e->getMessage();
        exit;
    }

    // Build the nested array while there are still rows to be fetched
    while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $item[$row['role']][] = $row['fullname'];
    }

    return $item;
}

function get_item_html($item)
{
    $output = "<li><a href='details.php?id="
        . $item['media_id'] . "'><img src='"
        . $item["img"] . "' alt='"
        . $item["title"] . "' />"
        . "<p>View Details</p>"
        . "</a></li>";
    return $output;
}

function array_category($catalog, $category)
{
    $output = array();

    foreach ($catalog as $id => $item) {
        if ($category == null or strtolower($category) == strtolower($item["category"])) {
            $sort = $item["title"];
            $sort = ltrim($sort, "The ");
            $sort = ltrim($sort, "A ");
            $sort = ltrim($sort, "An ");
            $output[$id] = $sort;
        }
    }

    asort($output);
    return array_keys($output);
}
