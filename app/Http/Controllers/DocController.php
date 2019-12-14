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
        UploadDocElement::truncate();

        $pieces = $this->splitMarkDown($markdownValue);

        foreach($pieces as $piece){
            $newOriginDocElementData = $this->isHeading($piece);
            
            $newOriginDocElement = new OriginDocElement( $newOriginDocElementData );
            $newOriginDocElement->save();
        }        
    }


    protected function splitMarkDown($markdownValue)
    {
        $pieces = explode("\n\n", $markdownValue);
        return $pieces;
    }


    protected function isHeading($text)
    {
        $header1Pattern = '/^#\s([\w\s]*)/';    // "# Some heading1"
        $header2Pattern = '/^##\s([\w\s]*)/';   // "## Some heading2"

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

        return [
            'value' => $text,
            'font_size' => 3
        ];

    }


    public function tmp()
    {
        $str = "## Functions";
        $pattern1 = '/^#\s([\w\s]*)/';
        // $pattern = '/[#]*/';

        preg_match($pattern1, $str, $matches);
        return $matches;
    }
}
