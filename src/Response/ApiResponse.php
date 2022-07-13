<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends Response
{
    public function __construct(string $content = "", int $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, []);

        switch ($headers['Accept'] ?? 'empty') {
            case 'application/json':
                $this->setContent($content);
                $this->headers->set('Content-Type', 'application/json');
                break;
            case 'application/xml':
                $secret = json_decode($content);
                $xml = '<?xml version="1.0" encoding="utf-8"?>';
                $xml .= '<Secret>';
                $xml .= '<hash>' . $secret->hash . '</hash>';
                $xml .= '<secretText>' . $secret->secretText . '</secretText>';
                $xml .= '<createdAt>' . $secret->createdAt . ' </createdAt>';
                $xml .= '<expiresAt>' . $secret->expiresAt . '</expiresAt>';
                $xml .= '<remainingViews>' . $secret->remainingViews . ' </remainingViews>';
                $xml .= '</Secret>';
                $this->setContent($xml);
                break;
            case '*/*':
                //empty
                $this->setContent($content);
                $this->headers->set('Content-Type', 'application/json');
                break;
            case $headers['Accept']:
                $this->setContent('The ' . $headers['Accept'] . ' return method is not yet implemented');
                break;

        }
    }
}
