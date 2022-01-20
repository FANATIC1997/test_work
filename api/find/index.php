<?php
// необходимые HTTP-заголовки
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';
include_once '../object/projects.php';

$database = new Database();
$db = $database->getConnection();
$project = new Projects($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['name']))
    {
        $search_text = trim($_POST['name']);
        $url = 'https://api.github.com/search/repositories?q='.$search_text.':php';

        $row = $project->find($url);
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
                $project->create($url, $string);
                $arResult['data'] = $data['items'];
            }
        }

        $result = [];
        if(!empty($arResult['data']))
        {
            foreach ($arResult['data'] as $json)
            {
                $ar['NameProject'] = $json['name'];
                $ar['Author'] = $json['owner']['login'];
                $ar['Stargazers'] = $json['stargazers_count'];
                $ar['Watchers'] = $json['watchers_count'];
                $ar['Link'] = $json['html_url'];
                $arResult[] = $ar;
            }
            // устанавливаем код ответа - 200 OK
            http_response_code(200);

            // выводим данны
            echo json_encode($arResult);
        }
    }
}
elseif($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $project->read();

    // проверка, найдено ли больше 0 записей
    if (count($stmt) > 0) {

        $arResult = [];
        foreach ($stmt as $key=>$item)
        {
            $result_json = unserialize($item['result']);
            $ar = [];
            foreach ($result_json as $json)
            {
                $ar['NameProject'] = $json['name'];
                $ar['Author'] = $json['owner']['login'];
                $ar['Stargazers'] = $json['stargazers_count'];
                $ar['Watchers'] = $json['watchers_count'];
                $ar['Link'] = $json['html_url'];
            }
            $arResult[] = $ar;
        }
        // устанавливаем код ответа - 200 OK
        http_response_code(200);

        // выводим данны
        echo json_encode($arResult);
    }
    else
    {
        // установим код ответа - 404 Не найдено
        http_response_code(404);

        // сообщаем пользователю, что не найдено
        echo json_encode(array("message" => "Проекты не найдены."), JSON_UNESCAPED_UNICODE);
    }
}

