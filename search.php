<?php

$arResult = [];
if(isset($_POST['search']))
{
    $conn = new mysqli('192.168.0.2', 'root', '127000', 'db_work');

    // Check connection
    if ($conn->connect_error) {
        $arResult['success'] = "Connection failed: " . $conn->connect_error;
    }

    $search_text = trim($_POST['name']);
    $url = 'https://api.github.com/search/repositories?q='.$search_text.':php';

    $result_search = $conn->query('SELECT * FROM projects WHERE url="'.$url.'"');
    $row = $result_search->fetch_assoc();
    if(!is_null($row))
    {
        $arResult['data'] = unserialize($row['result']);
    }
    else
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'User-Agent: FANATIC'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        curl_close($ch);
        $data = json_decode($result, true);
        if($data['total_count'] == 0)
        {
            $arResult['success'] = false;
        }
        else
        {
            $string = str_replace("'", " ", serialize($data['items']));
            $conn->query("INSERT INTO projects(url, result) VALUES ('".$url."', '".$string."')");
            $arResult['data'] = $data['items'];
        }
        //$arResult['data'] = $data->items;
    }
    $conn->close();
    $arResult['success'] = true;
}
else $arResult['success'] = false;
?>
<!doctype html>
<html lang="ru">
<head>
    <!doctype html>
    <html lang=ru>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
              integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link href="style.css" rel="stylesheet">
        <title>Поиск по Github</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
<body>
<div class="container-md overflow-hidden px-5">
    <div class="content p-3 clearfix border bg-light">
        <form action="search.php" method="post" id="formSearchProject">
            <div class="row-md">
                <label for="exampleInputName" class="label">Поиск репозитория</label>
            </div>
            <div class="row-md">
                <input class="form-control" type="text" name="name" placeholder="Введите название проекта..." value="<?=$search_text?>">
            </div>
            <div class="row-md">
                <button type="submit" class="btn btn-primary" name="search">Поиск</button>
            </div>
        </form>
    </div>
    <div class="content">
    <h2 align="center">Результат поиска</h2>
    <? if($arResult['success'] and !empty($arResult['data'])): ?>
    <? foreach (array_chunk($arResult['data'], 3) as $data): ?>
        <div class="row">
        <? foreach ($data as $item): ?>
            <div class="col-3">
            <div class="card">
                <img class="card-img-top" src="<?=$item['owner']['avatar_url']?>" alt="Card image cap">
                <div class="card-body">
                    <h5 class="card-title"><?=$item['name']?></h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Author: <?=$item['owner']['login']?></li>
                        <li class="list-group-item">Stargazer: <?=$item['stargazers_count']?></li>
                        <li class="list-group-item">Watchers: <?=$item['watchers_count']?></li>
                    </ul>
                    <a href="<?=$item['html_url']?>" class="btn btn-primary" target="_blank">Go repos</a>
                </div>
            </div>
            </div>
        <? endforeach; ?>
        </div>
    <? endforeach; ?>
    <? else: ?>
        <p>Не чего не найдено</p>
    <? endif; ?>
    <a href="/" class="back_link">Вернуться</a>
    </div>
</div>
</body>
</html>
