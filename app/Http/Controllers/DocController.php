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
        $elements = factory(UploadDocElement::class, 5)->make();
        
        // $tmpUploadDocElements = UploadDocElement::get();
        $originDocElements = OriginDocElement::get();
        
        return response()->json([
            "title" => "the title",
            "contents" => $originDocElements
        ]);
    }


    public function NewgetNowDocElements(){
        $originDocElements = \App\OriginDocElement::get();

        return $originDocElements;
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
            
            OriginDocElement::truncate();
            $newOriginDocElement = new OriginDocElement([
                'value' => $res,
                'font_size' => 3
            ]);
            $newOriginDocElement->save();

        
            // $my_html = Markdown::defaultTransform($res);
            // return $my_html;

            // $html = new \Html2Text\Html2Text($my_html);
            // return $html->getText();
        } catch (RequestException $e) {
            return response(409);
        }
    }
}
