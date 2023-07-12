<?php

namespace App\Http\Controllers;



use App\Models\Document;
use Spatie\PdfToText\Pdf;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Storage;


class DocumentController extends Controller
{
    public function index() {
        
        return view('welcome');
    }


    public function store(Request $request)
{
    $uploadedFile = $request->file('documentFile');
    $uploadedFileName = $uploadedFile->getClientOriginalName();

    $uploadedFilePath = $uploadedFile->store('documents');

    $request->session()->put('uploadedFileName', $uploadedFileName);
    $request->session()->put('uploadedFilePath', $uploadedFilePath);

    $pdfParser = new Parser();
    $pdf = $pdfParser->parseFile($uploadedFile->path());
    $context = $pdf->getText();

    return response()->json(['message' => 'File uploaded successfully', 'context' => $context], 200);
}


    public function search(Request $request)
    {
        $searchKeywords = $request->input('search');
    $uploadedFilePath = $request->session()->get('uploadedFilePath');
    $context = file_get_contents(storage_path('app/'.$uploadedFilePath));

    $searchResults = '';

    if (stripos($context, $searchKeywords) !== false) {
        $searchResults = '<p>Keywords found in the document:</p>';
        $searchResults .= '<p>' . nl2br($context) . '</p>';

        // Highlight the searched keyword
        $searchResults = str_ireplace($searchKeywords, '<span style="color: red;">'.$searchKeywords.'</span>', $searchResults);
    } else {
        $searchResults = '<p>Keywords not found in the document.</p>';
    }

    return $searchResults;
    }

    

    public function file() {
        return view('upload');
    }
}