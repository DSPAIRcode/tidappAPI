<?php

declare (strict_types=1);

/**
 * Klass för hantering av svar som ska skickas till klient
 *
 * @author kjellh
 */
class Response {

    public function __construct(private string|array|stdClass $content, private int $status = 200) {
        
    }

    public function getContent(): string|array|stdClass {
        return $this->content;
    }

    public function getStatus(): int {
        return $this->status;
    }

    public function setContent(string|array|stdClass $content): void {
        $this->content = $content;
    }

    public function setStatus(int $status): void {
        $this->status = $status;
    }

    /**
     * Skickar response-svaret som en JSON 
     * @param Response $response
     * @return never
     */
    public function skickaJSON(): never {
        http_response_code($this->status);
        header("content-type: application/json; charset=UTF-8");
        $json = json_encode($this->content, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
        echo $json;
        exit;
    }

    /**
     * Konverterar status-koden till en HTTP-header
     * @return string
     */
    private function getStatusMeddelande(): string {
        switch ($this->status) {
            case 200:
                return "HTTP/1.1 200 OK";
            case 400:
                return "HTTP/1.1 400 Bad request";
            case 401:
                return "HTTP/1.1 401 Unauthorized";
            case 403:
                return "HTTP/1.1 403 Forbidden";
            case 404:
                return "HTTP/1.1 403 Not found";
            case 405:
                return "HTTP/1.1 405 Method not allowed";
            default:
                return "HTTP/1.1 500 Internal Server Error";
        }
    }
}
