<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UploadDocElement;
use App\OriginDocElement;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

class DocController extends Controller
{
    public function getNowDocElements(){
        $originDocElements = OriginDocElement::get();
        
        return response()->json([
            "title" => "the title",
            "contents" => $originDocElements
        ]);
    }


    public function reFreshMDFromDoc(Request $request){
        $url = "https://raw.githubusercontent.com/JetBrains/kotlin-web-site/master/pages/docs/reference/basic-syntax.md";
        

        $client = new Client;
        $headers = [
            'headers' => [
                "Connection" => "keep-alive",
            ],
        ];

        try {
            $res = (string) $client->get($url, $headers)
                                ->getBody();
            
            $this->refreshDocElements($res);

        
            // $my_html = Markdown::defaultTransform($res);
            // return $my_html;

            // $html = new \Html2Text\Html2Text($my_html);
            // return $html->getText();
        } catch (RequestException $e) {
            return response(409);
        }
    }



    protected function refreshDocElements($markdownValue)
    {
        OriginDocElement::truncate();

        $pieces = $this->splitMarkDown($markdownValue);

        foreach($pieces as $piece){
            $newOriginDocElement = new OriginDocElement([
                'value' => $piece,
                'font_size' => 3
            ]);
            $newOriginDocElement->save();
        }        
    }


    protected function splitMarkDown($markdownValue)
    {
        $pieces = explode("\n\n", $markdownValue);
        return $pieces;
    }

}
