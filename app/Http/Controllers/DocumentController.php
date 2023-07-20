<?php

namespace App\Http\Controllers;



use App\Models\Document;
use Spatie\PdfToText\Pdf;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\Element\TextRun;
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

    // Check the file type to determine if it is a PDF or Word document
    if ($uploadedFile->getClientOriginalExtension() === 'pdf') {
        // Handle PDF file
        $pdfParser = new Parser();
        $pdf = $pdfParser->parseFile($uploadedFile->path());
        $context = $pdf->getText();
        $lines = explode("\n", $context);
    } elseif ($uploadedFile->getClientOriginalExtension() === 'docx' || $uploadedFile->getClientOriginalExtension() === 'doc') {
        // Handle Word document
        $phpWord = IOFactory::load($uploadedFile->path());
        $sections = $phpWord->getSections();
        $context = '';
        foreach ($sections as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof TextRun) {
                    foreach ($element->getElements() as $text) {
                        if ($text instanceof \PhpOffice\PhpWord\Element\Text) {
                            $context .= $text->getText() . " ";
                        }
                    }
                }
            }
        }
        $lines = explode("\n", $context);
    } else {
        // Invalid file type
        return response()->json(['error' => 'Invalid file type. Only PDF and Word documents are allowed.'], 400);
    }

    // Serialize and store the lines in the database
    $serializedLines = serialize($lines);

    // Assuming you have a "documents" table with "filename" and "content" columns
    $doc = new Document;
    $doc->filename = $uploadedFileName;
    $doc->content = $serializedLines;
    $doc->save();

    return response()->json(['message' => 'File uploaded successfully'], 200);
}


public function search(Request $request)
{
    $searchKeywords = $request->input('search');

    // Fetch all documents containing the search keywords in either filename or content
    $searchResults = DB::table('documents')
                        ->where('filename', 'LIKE', '%' . $searchKeywords . '%')
                        ->orWhere('content', 'LIKE', '%' . $searchKeywords . '%')
                        ->get();

    $output = [];

    if ($searchResults->isNotEmpty()) {
        foreach ($searchResults as $result) {
            if (!empty($result->content)) {
                $content = @unserialize($result->content); // Unserialize the data
                if (is_array($content)) {
                    $textDetails = '';
                    foreach ($content as $value) {
                        if (is_string($value)) {
                            $textDetails .= str_replace('\t', "\n", $value) . ' '; // Replace '\t' with '\n' and concatenate the text details
                        }
                    }

                    // Highlight the search term in the text details
                    $highlightedContent = str_ireplace($searchKeywords, '<span style="color: red">' . $searchKeywords . '</span>', $textDetails);
                    $output[] = [
                        'filename' => $result->filename,
                        'content' => $highlightedContent,
                    ];
                }
            }
        }
    } else {
        $output[] = ['error' => 'No results found.'];
    }

    return response()->json(['results' => $output]);
}


    public function file() {
        return view('upload');
    }



}