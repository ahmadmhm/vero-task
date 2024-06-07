<?php

class Core
{
    const BASIC_TOKEN = "QVBJX0V4cGxvcmVyOjEyMzQ1NmlzQUxhbWVQYXNz";
    public function __construct(protected string $token = '')
    {
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }
    public function fetchData()
    {
        $response = $this->getData();
        if($response['is_success']) {
            return $response;
        } else {
            if (!empty($response['code']) && $response['code'] == 401) {
                $token = $this->authenticate();
                if($token) {
                    $this->setToken($token);
                    return $this->fetchData();
                } else {
                    return ['is_success'=> false, 'data' => []];
                }
            }
        }
    }
    protected function authenticate(): ?string
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.baubuddy.de/index.php/login",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"username\":\"365\", \"password\":\"1\"}",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Basic ".self::BASIC_TOKEN,
            ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return null;
        }
        $response = json_decode($response);
        if (!empty($response->oauth)) {
            return $response->oauth->access_token;
        }
        return null;
    }

    protected function getData(): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.baubuddy.de/dev/index.php/v1/tasks/select",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer {$this->getToken()}",
            ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return ['is_success'=> false, "message" => $err];
        }
        $response = json_decode($response);
        if (isset($response->error) && $response->error->code != 200) {

            return ['is_success'=> false, 'message' => $response->error->message, 'code' => $response->error->code];
        } else {

            return ['is_success'=> true, 'data' => $this->mapData($response)];
        }
    }
    protected function mapData($objectData): array
    {
        $data = [];
        foreach ($objectData as $key => $row) {
            $data [] = [
                'task' => $row->task,
                'title' => $row->title,
                'description' => $row->description,
                'colorCode' => $row->colorCode,
            ];
        }

        return $data;
    }
}