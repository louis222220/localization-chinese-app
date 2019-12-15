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


    public function uploadDocElement(Request $request)
    {
        $validatedData = $request->validate([
            'origin_element_id' => [
                'required',
                'integer',
                'exists:origin_doc_elements,id'
            ],
            'value' => ['required', 'string'],
        ]);

        $newUploadDocElement = new UploadDocElement($validatedData);
        $newUploadDocElement->save();
        return response()->json([
            'message' => '成功上傳翻譯'
        ], 201);
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
            $resWithoutTag = strip_tags($res);
            $this->refreshDocElements($resWithoutTag);

        
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
        // UploadDocElement::truncate();

        $pieces = $this->splitMarkDown($markdownValue);

        foreach($pieces as $piece){
            $newOriginDocElementData = $this->isHeading($piece);
            if(! $newOriginDocElementData){
                continue;
            }
            
            $newOriginDocElement = new OriginDocElement( $newOriginDocElementData );
            $newOriginDocElement->save();
        }
    }


    protected function splitMarkDown($markdownValue)
    {
        $pieces = explode("\n\n", $markdownValue);
        foreach($pieces as $piece){
            $piece = trim($piece);
        }
        return $pieces;
    }


    protected function isHeading($text)
    {
        $header1Pattern = '/^#\s([\w\s]*)/';    // "# Some heading1"
        $header2Pattern = '/^##\s([\w\s]*)/';   // "## Some heading2"
        $titleInfoPattern = '/^---/';

        preg_match($header1Pattern, $text, $matches);
        if (count($matches)){
            return [
                'value' => $matches[1],
                'font_size' => 1
            ];
        }
        
        preg_match($header2Pattern, $text, $matches);
        if (count($matches)){
            return [
                'value' => $matches[1],
                'font_size' => 2
            ];
        }
        
        preg_match($titleInfoPattern, $text, $matches);
        if (count($matches)){
            return null;
        }
        if (! $text){
            return null;
        }
        

        return [
            'value' => $text,
            'font_size' => 3
        ];

    }


    public function tmp()
    {
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
        } catch (RequestException $e) {
            return response(409);
        }
        
        $pieces = $this->splitMarkDown($res);
        // return $pieces;
        // $str = "## Functions";
        $pattern1 = '/^---*/';
        // $pattern = '/[#]*/';

        preg_match($pattern1, $pieces[1], $matches);
        return $matches;
    }
}
