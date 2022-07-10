<?php
namespace App\Response;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends Response
{
    public function __construct(Request $request,array $content = [], int $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);
       /*switch($request->headers->get('accept')){
            case 'application/json':*/
                $this->setContent(json_encode($content));
               /* break;
            case 'application/xml':
                $xml = '<?xml version="1.0" encoding="utf-8"?>';
                $xml .= '<Secret>';
                $xml .= '<hash>string</hash>';
                $xml .= '<secretText>string</secretText>';
                $xml .= '<createdAt>2022-07-10T10:43:22.205Z</createdAt>';
                $xml .= '<expiresAt>2022-07-10T10:43:22.205Z</expiresAt>';
                $xml .= '<remainingViews>0</remainingViews>';
                $xml .= '</Secret>';
                $this->setContent($xml);
                break;
        }*/
    }

}