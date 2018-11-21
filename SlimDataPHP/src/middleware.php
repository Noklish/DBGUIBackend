<?php
header("Access-Control-Allow-Origin: *");
// Application middleware
$path = array(
    "/accounts",
    "/stories",
    "/equipment",
    "/vehicles",
    "/experts"
);

$app->add(new \Tuupola\Middleware\JwtAuthentication([
    "path" => $path, /* or ["/api", "/admin"] */
    "attribute" => "decoded_token_data",
    "secret" => "KEY",
    "algorithm" => ["HS256"],
    "error" => function ($response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));