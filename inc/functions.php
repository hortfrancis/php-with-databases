<?php

function get_catalog_count($category = null, $search = null)
{
    if (isset($category)) $category = strtolower($category);

    include('connection.php');

    try {
        $query = 'SELECT COUNT(media_id) FROM Media';
        if (!empty($search)) {
            $result = $conn->prepare(
                $query
                . " WHERE title LIKE ?"
            );
            $result->bindValue(1, '%' . $search . '%', PDO::PARAM_STR);
        } elseif (!empty($category)) {
            $result = $conn->prepare(
                $query
                    . ' WHERE LOWER(category) = ?'
            );
            $result->bindParam(1, $category, PDO::PARAM_STR);
        } else {
            $result = $conn->prepare($query);
        }
        $result->execute();
    } catch (Exception $e) {
        echo 'Bad query: ' . $e->getMessage();
    }

    $count = $result->fetchColumn(0);
    return $count;
}

function full_catalog_array($limit = null, $offset = 0)
{
    include('connection.php');

    try {

        $query = "
            SELECT media_id, title, category, img 
            FROM Media
            ORDER BY 
            REPLACE(
                REPLACE(
                    REPLACE(title,'The ',''),
                    'An ',
                    ''
                ),
                'A ',
                ''
            )";
        if (is_integer($limit)) {
            $results = $conn->prepare($query . " LIMIT ? OFFSET ?");
            $results->bindParam(1, $limit, PDO::PARAM_INT);
            $results->bindParam(2, $offset, PDO::PARAM_INT);
        } else {
            $results = $conn->prepare($sql);
            echo 'in else';
        }
        $results->execute();
    } catch (Exception $e) {
        echo 'Could not retrieve data: ' . $e->getMessage();
        exit;
    }

    $catalog = $results->fetchAll();
    return $catalog;
}

function category_catalog_array($category, $limit = null, $offset = 0)
{
    include('connection.php');

    strtolower($category);

    try {
        // Returning a PDOStatement object
        $query = "
        SELECT media_id, title, category, img 
        FROM Media
        WHERE LOWER(category) = ?
        ORDER BY 
        REPLACE(
            REPLACE(
                REPLACE(title,'The ',''),
                'An ',
                ''
            ),
            'A ',
            ''
        )";
        if (is_integer($limit)) {
            $results = $conn->prepare($query . ' LIMIT ? OFFSET ?');
            $results->bindParam(1, $category, PDO::PARAM_STR);
            $results->bindParam(2, $limit, PDO::PARAM_INT);
            $results->bindParam(3, $offset, PDO::PARAM_INT);
        } else {
            $results = $conn->prepare($query);
            $results->bindParam(1, $category, PDO::PARAM_STR);
        }
        $results->execute();
    } catch (Exception $e) {
        echo 'Could not retrieve data: ' . $e->getMessage();
        exit;
    }

    $catalog = $results->fetchAll();
    return $catalog;
}

function search_catalog_array($search, $limit = null, $offset = 0)
{
    include('connection.php');

    strtolower($search);

    try {
        // Returning a PDOStatement object
        $query = "
        SELECT media_id, title, category, img 
        FROM Media
        WHERE title LIKE ?
        ORDER BY 
        REPLACE(
            REPLACE(
                REPLACE(title,'The ',''),
                'An ',
                ''
            ),
            'A ',
            ''
        )";
        if (is_integer($limit)) {
            $results = $conn->prepare($query . ' LIMIT ? OFFSET ?');
            $results->bindValue(1, '%' . $search . '%', PDO::PARAM_STR);
            $results->bindParam(2, $limit, PDO::PARAM_INT);
            $results->bindParam(3, $offset, PDO::PARAM_INT);
        } else {
            $results = $conn->prepare($query);
            $results->bindValue(1, '%' . $search . '%', PDO::PARAM_STR);
        }
        $results->execute();
    } catch (Exception $e) {
        echo 'Could not retrieve data: ' . $e->getMessage();
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
        echo 'Could not retrieve data: ' . $e->getMessage();
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

function genre_array($category = null)
{
    // Normalise category string argument to lowercase
    if (isset($category)) {
        $category = strtolower($category);
    }

    // Access a connection to the database
    include('connection.php');

    try {
        $query = "
        SELECT genre, category
        FROM Genres
        JOIN Genre_Categories 
        ON Genres.genre_id = Genre_Categories.genre_id 
        ";
        if (!empty($category)) {
            $results = $conn->prepare($query
                . "WHERE LOWER(category) = ?"
                . "ORDER BY genre");
            $results->bindParam(1, $category, PDO::PARAM_STR);
        } else {
            $results = $conn->prepare($query . "ORDER BY genre");
        }
        $results->execute();
    } catch (Exception $e) {
        echo 'Bad SQL query: ' . $e->getMessage();
    }

    $genres = array();
    while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $genres[$row['category']][] = $row['genre'];
    }
    return $genres;
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
